<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mandazi;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        if (!$request->user()->isSeller()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sellerId = $request->user()->id;

        $stats = [
            'total_sales' => Mandazi::where('seller_id', $sellerId)
                ->where('status', 'Paid')
                ->sum('total_amount'),
            'pending_amount' => Mandazi::where('seller_id', $sellerId)
                ->where('status', 'Pending')
                ->sum('total_amount'),
            'total_orders' => Mandazi::where('seller_id', $sellerId)->count(),
            'paid_orders' => Mandazi::where('seller_id', $sellerId)
                ->where('status', 'Paid')
                ->count(),
            'pending_orders' => Mandazi::where('seller_id', $sellerId)
                ->where('status', 'Pending')
                ->count(),
            'failed_orders' => Mandazi::where('seller_id', $sellerId)
                ->where('status', 'Failed')
                ->count(),
            'total_customers' => Mandazi::where('seller_id', $sellerId)
                ->distinct('user_id')
                ->count('user_id'),
            'unique_customers' => Mandazi::where('seller_id', $sellerId)
                ->distinct('user_id')
                ->count('user_id'),
        ];

        return response()->json($stats);
    }

    public function allOrders(Request $request)
    {
        if (!$request->user()->isSeller()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sellerId = $request->user()->id;

        $orders = Mandazi::with(['user', 'payment', 'seller'])
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function allUsers(Request $request)
    {
        if (!$request->user()->isSeller()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sellerId = $request->user()->id;

        $users = User::where('role', 'buyer')
            ->whereHas('mandazi', function($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            })
            ->withCount(['mandazi' => function($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            }])
            ->withSum(['mandazi' => function($query) use ($sellerId) {
                $query->where('seller_id', $sellerId);
            }], 'total_amount')
            ->get();

        return response()->json($users);
    }

    public function userOrders(Request $request, $userId)
    {
        if (!$request->user()->isSeller()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sellerId = $request->user()->id;

        $orders = Mandazi::with('payment')
            ->where('user_id', $userId)
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function salesChart(Request $request)
    {
        if (!$request->user()->isSeller()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $sellerId = $request->user()->id;

        $sales = Mandazi::where('status', 'Paid')
            ->where('seller_id', $sellerId)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return response()->json($sales);
    }
}