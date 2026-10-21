<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class DashboardController extends Controller
{
    /**
     * Display admin dashboard home.
     */
    public function index()
    {
        try {
            // Metrics
            $totalRevenue = Order::where('status_payment', 'PAID')->sum('grand_total');
            $totalOrders = Order::count();
            $totalCustomers = User::where('role', 'customer')->count();

            // Best Seller products
            $bestSellers = DB::table('order_items')
                ->join('product_skus', 'order_items.product_sku_id', '=', 'product_skus.id')
                ->join('product_variants', 'product_skus.product_variant_id', '=', 'product_variants.id')
                ->join('products', 'product_variants.product_id', '=', 'products.id')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_sold', 'desc')
                ->take(5)
                ->get();

            // Recent orders
            $recentOrders = Order::with(['user'])
                ->latest()
                ->take(15)
                ->get();

            return Inertia::render('Admin/Dashboard', [
                'metrics' => [
                    'revenue' => (float)$totalRevenue,
                    'orders' => $totalOrders,
                    'customers' => $totalCustomers,
                ],
                'best_sellers' => $bestSellers,
                'recent_orders' => $recentOrders,
            ]);
        } catch (Exception $e) {
            Log::error('Admin dashboard loading exception: ' . $e->getMessage());
            return redirect()->route('customer.home')->with('error', 'Gagal memuat dashboard admin.');
        }
    }

    /**
     * Update waybill shipping number (Input Resi).
     */
    public function updateWaybill(Request $request, $id)
    {
        $request->validate([
            'tracking_number' => 'required|string|max:50',
        ]);

        try {
            $order = Order::findOrFail($id);
            
            $order->update([
                'tracking_number' => $request->tracking_number,
                'status_delivery' => 'SHIPPED', // Update shipping status
            ]);

            return redirect()->back()->with('success', "Nomor resi [{$request->tracking_number}] berhasil diperbarui.");
        } catch (Exception $e) {
            Log::error('Update waybill exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui nomor resi.');
        }
    }
}
