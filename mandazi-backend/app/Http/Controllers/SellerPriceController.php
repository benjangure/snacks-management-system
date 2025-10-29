<?php

namespace App\Http\Controllers;

use App\Models\SellerPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SellerPriceController extends Controller
{
    /**
     * Get current seller price
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isSeller()) {
            return response()->json(['message' => 'Only sellers can access this endpoint'], 403);
        }

        $price = SellerPrice::where('seller_id', $user->id)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'price' => $price ? $price->price_per_unit : null,
            'has_price' => $price !== null,
        ]);
    }

    /**
     * Set or update seller price
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isSeller()) {
            return response()->json(['message' => 'Only sellers can set prices'], 403);
        }

        $request->validate([
            'price_per_unit' => 'required|numeric|min:0.01',
        ]);

        // Deactivate existing prices
        SellerPrice::where('seller_id', $user->id)
            ->update(['is_active' => false]);

        // Create new active price
        $price = SellerPrice::create([
            'seller_id' => $user->id,
            'price_per_unit' => $request->price_per_unit,
            'is_active' => true,
        ]);

        Log::info('Seller price updated', [
            'seller_id' => $user->id,
            'price' => $request->price_per_unit,
        ]);

        return response()->json([
            'message' => 'Price updated successfully',
            'price' => $price->price_per_unit,
        ]);
    }
}
