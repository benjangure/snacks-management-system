<?php

namespace App\Http\Controllers;

use App\Models\Mandazi;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Get M-Pesa OAuth token with retry logic
     *
     * @return string|null
     */
    public function getAccessToken($retryCount = 0)
    {
        $maxRetries = 3;
        $url = config('mpesa.env') === 'sandbox'
            ? 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials'
            : 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        Log::info('M-Pesa Access Token Attempt', [
            'env' => config('mpesa.env'),
            'consumer_key' => config('mpesa.consumer_key') ? 'Set' : 'Missing',
            'consumer_secret' => config('mpesa.consumer_secret') ? 'Set' : 'Missing',
            'url' => $url,
            'attempt' => $retryCount + 1,
            'max_retries' => $maxRetries
        ]);

        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
                'connect_timeout' => 15,
            ])->withBasicAuth(
                config('mpesa.consumer_key'),
                config('mpesa.consumer_secret')
            )->get($url);

            Log::info('M-Pesa Access Token Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'successful' => $response->successful(),
                'attempt' => $retryCount + 1
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'] ?? null;
                if ($token) {
                    Log::info('✅ M-Pesa Access Token Success', ['attempt' => $retryCount + 1]);
                    return $token;
                }
            }

            // If failed and we have retries left
            if ($retryCount < $maxRetries - 1) {
                Log::warning("🔄 Retrying M-Pesa token request (attempt " . ($retryCount + 2) . "/$maxRetries)");
                sleep(2); // Wait 2 seconds before retry
                return $this->getAccessToken($retryCount + 1);
            }

            Log::error('❌ M-Pesa Access Token Failed After All Retries', [
                'status' => $response->status(),
                'body' => $response->body(),
                'attempts' => $maxRetries
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('M-Pesa Access Token Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attempt' => $retryCount + 1
            ]);

            // Retry on network errors
            if ($retryCount < $maxRetries - 1 && 
                (strpos($e->getMessage(), 'Could not resolve host') !== false || 
                 strpos($e->getMessage(), 'Connection timed out') !== false)) {
                Log::warning("🔄 Retrying due to network error (attempt " . ($retryCount + 2) . "/$maxRetries)");
                sleep(3); // Wait longer for network issues
                return $this->getAccessToken($retryCount + 1);
            }

            return null;
        }
    }

    /**
     * Initiate payment (STK Push) - public route requires auth:sanctum
     */
    public function processPayment(Request $request, $mandaziId)
    {
        // Ensure caller is authenticated
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        try {
            // Find mandazi that belongs to the user (buyer)
            $mandazi = Mandazi::where('user_id', $user->id)->findOrFail($mandaziId);

            if ($mandazi->status === 'Paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is already paid'
                ], 400);
            }

            $validated = $request->validate([
                'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            ]);

            $mpesaMode = config('mpesa.mode', 'auto');
            
            Log::info('🔄 Payment initiated', [
                'mandazi_id' => $mandaziId,
                'user_id' => $user->id,
                'amount' => $mandazi->total_amount,
                'phone' => $validated['phone_number'],
                'mpesa_mode' => $mpesaMode,
            ]);

            // Handle different modes
            switch ($mpesaMode) {
                case 'real':
                    // Force real STK push
                    $stkResult = $this->tryRealStkPush($mandazi, $validated['phone_number']);
                    if (is_array($stkResult) && ($stkResult['success'] ?? false)) {
                        return response()->json(array_merge($stkResult, ['mode' => 'real_stk_push']));
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'STK Push failed: ' . ($stkResult['error'] ?? 'Unknown error'),
                            'mode' => 'real_stk_failed'
                        ], 500);
                    }
                    
                case 'simulation':
                    // Force simulation
                    Log::info('🎭 Using simulation mode (forced)');
                    return $this->processRealisticPayment($mandazi, $validated['phone_number']);
                    
                case 'auto':
                default:
                    // Try real first, fallback to simulation
                    $stkResult = $this->tryRealStkPush($mandazi, $validated['phone_number']);
                    if (is_array($stkResult) && ($stkResult['success'] ?? false)) {
                        return response()->json(array_merge($stkResult, ['mode' => 'real_stk_push']));
                    }
                    
                    Log::warning('🔄 Real STK Push failed, falling back to simulation', [
                        'reason' => $stkResult['error'] ?? 'unknown'
                    ]);
                    return $this->processRealisticPayment($mandazi, $validated['phone_number']);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('💥 Payment process exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alias for route compatibility
     */
    public function initiatePayment(Request $request, $mandaziId)
    {
        return $this->processPayment($request, $mandaziId);
    }

    /**
     * Try a real STK Push. Returns an array with success flag OR array with success=false and error.
     */
    private function tryRealStkPush(Mandazi $mandazi, string $phoneNumber): array
    {
        try {
            $accessToken = $this->getAccessToken();

            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Failed to get M-Pesa access token'
                ];
            }

            $url = config('mpesa.env') === 'sandbox'
                ? 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'
                : 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

            $timestamp = date('YmdHis');
            $password = base64_encode(config('mpesa.shortcode') . config('mpesa.passkey') . $timestamp);

            $payload = [
                'BusinessShortCode' => config('mpesa.shortcode'),
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) $mandazi->total_amount,
                'PartyA' => $phoneNumber,
                'PartyB' => config('mpesa.shortcode'),
                'PhoneNumber' => $phoneNumber,
                'CallBackURL' => config('mpesa.callback_url'),
                'AccountReference' => 'Mandazi-' . $mandazi->id,
                'TransactionDesc' => 'Payment for Mandazi',
            ];

            Log::info('🔄 Attempting real STK Push', ['payload' => $payload]);

            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->withToken($accessToken)->post($url, $payload);

            $status = $response->status();
            $responseData = $response->json();

            Log::info('M-Pesa STK Push Response', [
                'status' => $status,
                'body' => $response->body(),
                'json' => $responseData
            ]);

            // Normal successful response contains ResponseCode === "0" (string or int)
            $responseCode = $responseData['ResponseCode'] ?? $responseData['responseCode'] ?? null;

            if ($response->successful() && ($responseCode === '0' || $responseCode === 0)) {
                $checkoutRequestId = $responseData['CheckoutRequestID'] ?? $responseData['checkoutRequestID'] ?? null;

                // Create payment record in pending state
                $payment = Payment::create([
                    'mandazi_id' => $mandazi->id,
                    'checkout_request_id' => $checkoutRequestId,
                    'amount' => $mandazi->total_amount,
                    'phone_number' => $phoneNumber,
                    'status' => 'Pending',
                    'mpesa_response' => json_encode($responseData),
                ]);

                return [
                    'success' => true,
                    'checkout_request_id' => $checkoutRequestId,
                    'message' => 'STK Push initiated'
                ];
            }

            // Non-success response: return error text if available
            $error = $responseData['ResponseDescription'] ?? $responseData['responseDescription'] ?? $response->body() ?? 'STK Push failed';
            return [
                'success' => false,
                'error' => $error
            ];

        } catch (\Exception $e) {
            Log::error('Real STK Push Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Realistic payment simulation fallback.
     * Returns a JsonResponse just like the real flow does.
     */
    private function processRealisticPayment(Mandazi $mandazi, string $phoneNumber)
    {
        // Create pending payment record
        $payment = Payment::create([
            'mandazi_id' => $mandazi->id,
            'checkout_request_id' => 'SIM_' . time() . '_' . $mandazi->id,
            'amount' => $mandazi->total_amount,
            'phone_number' => $phoneNumber,
            'status' => 'Pending',
            'mpesa_response' => json_encode([
                'initiated_at' => now()->toDateTimeString(),
                'amount' => $mandazi->total_amount,
                'phone' => $phoneNumber,
                'mode' => 'realistic_simulation'
            ])
        ]);

        Log::info('🔄 Starting realistic payment simulation', [
            'payment_id' => $payment->id,
            'mandazi_id' => $mandazi->id,
            'amount' => $mandazi->total_amount
        ]);

        try {
            sleep(8); // simulate processing

            DB::transaction(function () use ($mandazi, $payment) {
                $mandazi->update(['status' => 'Paid']);

                $payment->update([
                    'status' => 'Success',
                    'transaction_id' => 'SIM' . rand(1000000, 9999999),
                    'mpesa_response' => json_encode([
                        'Body' => [
                            'stkCallback' => [
                                'MerchantRequestID' => 'SIM_' . time(),
                                'CheckoutRequestID' => $payment->checkout_request_id,
                                'ResultCode' => 0,
                                'ResultDesc' => 'The service request is processed successfully.',
                                'CallbackMetadata' => [
                                    'Item' => [
                                        ['Name' => 'Amount', 'Value' => $mandazi->total_amount],
                                        ['Name' => 'MpesaReceiptNumber', 'Value' => 'SIM' . rand(1000000, 9999999)],
                                        ['Name' => 'TransactionDate', 'Value' => date('YmdHis')],
                                        ['Name' => 'PhoneNumber', 'Value' => $payment->phone_number]
                                    ]
                                ]
                            ]
                        ]
                    ])
                ]);
            });

            Log::info('✅ Realistic payment simulation completed', [
                'mandazi_id' => $mandazi->id,
                'payment_id' => $payment->id,
                'transaction_id' => $payment->fresh()->transaction_id
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Payment simulation failed', [
                'error' => $e->getMessage(),
                'mandazi_id' => $mandazi->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully via simulation!',
            'status' => 'paid',
            'transaction_id' => $payment->transaction_id,
            'mode' => 'simulation',
            'note' => 'Real M-Pesa API unavailable, used simulation mode'
        ]);
    }

    /**
     * M-Pesa callback handler for STK Push
     */
    public function handleCallback(Request $request)
    {
        // Use raw body in logs to avoid lost JSON structure in some cases
        $raw = $request->getContent();
        Log::info('🎯 M-Pesa CALLBACK RECEIVED - RAW', ['body' => substr($raw, 0, 2000)]);

        try {
            // Accept JSON payload robustly
            $callbackData = $request->json()->all();
            if (empty($callbackData)) {
                // Fallback to decoding raw body
                $callbackData = json_decode($raw, true) ?? [];
            }

            // Check for stkCallback in different possible structures
            $stkCallback = $callbackData['Body']['stkCallback'] ?? $callbackData['body']['stkCallback'] ?? null;

            if (!$stkCallback) {
                Log::error('❌ Invalid callback format - No stkCallback found', ['callback' => $callbackData]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback format']);
            }

            $resultCode = $stkCallback['ResultCode'] ?? null;
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? $stkCallback['checkoutRequestID'] ?? null;
            $resultDesc = $stkCallback['ResultDesc'] ?? ($stkCallback['resultDesc'] ?? null);

            Log::info('🔍 Processing callback', [
                'resultCode' => $resultCode,
                'checkoutRequestId' => $checkoutRequestId,
                'resultDesc' => $resultDesc
            ]);

            if (!$checkoutRequestId) {
                Log::warning('⚠️ No CheckoutRequestID in callback', ['callback' => $stkCallback]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'No CheckoutRequestID']);
            }

            // Find payment by checkout_request_id
            $payment = Payment::where('checkout_request_id', $checkoutRequestId)->first();

            if (!$payment) {
                Log::warning('⚠️ Payment not found for callback', [
                    'checkout_request_id' => $checkoutRequestId,
                    'available_payments' => Payment::pluck('checkout_request_id')->take(50)->toArray()
                ]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Payment not found']);
            }

            // Successful payment handling
            if ($resultCode === 0 || $resultCode === '0') {
                DB::transaction(function () use ($payment, $stkCallback) {
                    if ($payment->mandazi) {
                        $payment->mandazi->update(['status' => 'Paid']);
                    }

                    $payment->update([
                        'status' => 'Success',
                        'mpesa_response' => json_encode($stkCallback)
                    ]);

                    // Extract MpesaReceiptNumber if present
                    $items = $stkCallback['CallbackMetadata']['Item'] ?? $stkCallback['callbackMetadata']['Item'] ?? null;
                    if (is_array($items)) {
                        foreach ($items as $item) {
                            if (($item['Name'] ?? '') === 'MpesaReceiptNumber' || ($item['name'] ?? '') === 'MpesaReceiptNumber') {
                                $payment->update(['transaction_id' => $item['Value'] ?? $item['value'] ?? null]);
                                break;
                            }
                        }
                    }
                });

                Log::info('✅ Payment confirmed via callback', [
                    'payment_id' => $payment->id,
                    'mandazi_id' => $payment->mandazi_id,
                    'checkout_request_id' => $checkoutRequestId
                ]);

            } else {
                // Failure update
                $payment->update([
                    'status' => 'Failed',
                    'mpesa_response' => json_encode($stkCallback)
                ]);

                Log::error('❌ Payment failed via callback', [
                    'payment_id' => $payment->id,
                    'resultCode' => $resultCode,
                    'resultDesc' => $resultDesc
                ]);
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback processed successfully']);

        } catch (\Exception $e) {
            Log::error('💥 Callback processing error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'raw_body' => substr($raw, 0, 2000)
            ]);

            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Callback processing failed']);
        }
    }

    /**
     * Check status of order
     */
    public function checkStatus($mandaziId, Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $mandazi = Mandazi::where('user_id', $user->id)->findOrFail($mandaziId);

            return response()->json([
                'success' => true,
                'status' => $mandazi->status,
                'order_id' => $mandazi->id,
                'amount' => $mandazi->total_amount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testCallback(Request $request, $checkoutRequestId = null)
    {
        Log::info('🧪 TEST CALLBACK RECEIVED', [
            'checkout_request_id' => $checkoutRequestId,
            'payload' => $request->all()
        ]);

        // If checkout request ID is provided, simulate a real callback
        if ($checkoutRequestId) {
            $simulatedCallback = [
                'Body' => [
                    'stkCallback' => [
                        'MerchantRequestID' => 'TEST_' . time(),
                        'CheckoutRequestID' => $checkoutRequestId,
                        'ResultCode' => 0,
                        'ResultDesc' => 'Test callback - payment successful',
                        'CallbackMetadata' => [
                            'Item' => [
                                ['Name' => 'Amount', 'Value' => 100.00],
                                ['Name' => 'MpesaReceiptNumber', 'Value' => 'TEST' . rand(1000000, 9999999)],
                                ['Name' => 'TransactionDate', 'Value' => date('YmdHis')],
                                ['Name' => 'PhoneNumber', 'Value' => 254700000000]
                            ]
                        ]
                    ]
                ]
            ];

            // Process through the real callback handler
            $testRequest = new Request();
            $testRequest->merge($simulatedCallback);
            
            Log::info('🔄 Processing simulated callback through real handler');
            $result = $this->handleCallback($testRequest);
            
            return response()->json([
                'message' => 'Test callback processed',
                'checkout_request_id' => $checkoutRequestId,
                'callback_result' => $result->getData(),
                'simulated_payload' => $simulatedCallback
            ]);
        }

        return response()->json(['message' => 'Test callback received successfully']);
    }
}
