<?php

namespace App\Http\Controllers;

use App\Models\Mandazi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MandaziController extends Controller
{
    /**
     * List mandazi orders for authenticated user.
     * Sellers see orders for them; buyers see their own orders.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $mandazi = $user->isSeller()
            ? Mandazi::with('user')
                ->where('seller_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get()
            : Mandazi::with('seller')
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

        return response()->json($mandazi);
    }

    /**
     * Create a new mandazi order.
     */
    public function store(Request $request)
    {
        Log::info('🔍 MANDAZI STORE METHOD CALLED', [
            'user_id' => $request->user()->id,
            'request_data' => $request->all(),
        ]);

        $request->validate([
            'quantity' => 'required|integer|min:1',
            'phone_number' => 'required|string|regex:/^254[0-9]{9}$/',
            'seller_id' => 'required|exists:users,id',
        ]);

        // Verify seller exists and is a seller
        $seller = User::where('id', $request->seller_id)
            ->where('role', 'seller')
            ->with('sellerPrice')
            ->first();

        if (!$seller) {
            return response()->json([
                'message' => 'Selected seller not found or is not a valid seller'
            ], 422);
        }

        // Check if seller has set a price
        if (!$seller->sellerPrice) {
            return response()->json([
                'message' => 'Selected seller has not set a price yet. Please contact the seller.'
            ], 422);
        }

        $price_per_unit = $seller->sellerPrice->price_per_unit;
        $total_amount = $request->quantity * $price_per_unit;

        $mandazi = Mandazi::create([
            'user_id' => $request->user()->id,
            'seller_id' => $request->seller_id,
            'quantity' => $request->quantity,
            'price_per_unit' => $price_per_unit,
            'total_amount' => $total_amount,
            'phone_number' => $request->phone_number,
            'status' => 'Pending',
        ]);

        return response()->json($mandazi, 201);
    }

    /**
     * Show a single mandazi order.
     */
    public function show($id)
    {
        $mandazi = Mandazi::with(['user', 'seller'])->findOrFail($id);
        return response()->json($mandazi);
    }

    /**
     * Delete a mandazi order (only pending and owned by user).
     */
    public function destroy($id)
    {
        $mandazi = Mandazi::findOrFail($id);

        if ($mandazi->status !== 'Pending' || $mandazi->user_id !== auth()->id()) {
            return response()->json(['message' => 'Cannot delete this record'], 403);
        }

        $mandazi->delete();
        return response()->json(['message' => 'Record deleted successfully']);
    }

    /**
     * Stats for dashboard.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        if ($user->isSeller()) {
            $stats = [
                'total_sales' => Mandazi::where('seller_id', $user->id)->where('status', 'Paid')->sum('total_amount'),
                'pending_amount' => Mandazi::where('seller_id', $user->id)->where('status', 'Pending')->sum('total_amount'),
                'total_orders' => Mandazi::where('seller_id', $user->id)->count(),
                'paid_orders' => Mandazi::where('seller_id', $user->id)->where('status', 'Paid')->count(),
                'unique_customers' => Mandazi::where('seller_id', $user->id)->distinct('user_id')->count('user_id'),
            ];
        } else {
            $stats = [
                'total_spent' => Mandazi::where('user_id', $user->id)->where('status', 'Paid')->sum('total_amount'),
                'pending_amount' => Mandazi::where('user_id', $user->id)->where('status', 'Pending')->sum('total_amount'),
                'total_orders' => Mandazi::where('user_id', $user->id)->count(),
                'paid_orders' => Mandazi::where('user_id', $user->id)->where('status', 'Paid')->count(),
            ];
        }

        return response()->json($stats);
    }

    /**
     * Return all sellers for buyer dropdown.
     */
    public function getSellers()
    {
        $sellers = User::where('role', 'seller')
            ->with('sellerPrice')
            ->select('id', 'name')
            ->get()
            ->map(function ($seller) {
                return [
                    'id' => $seller->id,
                    'name' => $seller->name,
                    'price_per_unit' => $seller->sellerPrice ? $seller->sellerPrice->price_per_unit : null,
                    'has_price' => $seller->sellerPrice !== null,
                ];
            });

        return response()->json($sellers);
    }

    /**
     * Public mandazi orders (no auth required)
     */
    public function publicIndex()
    {
        $orders = Mandazi::with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
            'message' => 'Orders retrieved successfully (public)',
        ]);
    }
}
