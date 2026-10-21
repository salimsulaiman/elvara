<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class OrderController extends Controller
{
    protected RajaOngkirService $rajaOngkir;

    public function __construct(RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    /**
     * Display list of customer order history.
     */
    public function index()
    {
        try {
            $orders = Order::with(['items.sku.variant.product'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            return Inertia::render('Customer/Order/Index', [
                'orders' => $orders,
            ]);
        } catch (Exception $e) {
            Log::error('Order history loading exception: ' . $e->getMessage());
            return redirect()->route('customer.home')->with('error', 'Gagal memuat riwayat pesanan.');
        }
    }

    /**
     * Display order details & shipping tracking.
     */
    public function tracking($reference)
    {
        try {
            $order = Order::with(['items.sku.variant.product.category', 'user'])
                ->where('order_reference', $reference)
                ->firstOrFail();

            // Security check: must belong to the user
            if ($order->user_id !== Auth::id()) {
                return redirect()->route('customer.order.index')->with('error', 'Aksi tidak diizinkan.');
            }

            // Fetch live tracking if waybill exists
            $trackingData = null;
            if ($order->tracking_number) {
                $trackingData = $this->rajaOngkir->trackWaybill(
                    $order->tracking_number,
                    $order->shipping_courier
                );
            }

            return Inertia::render('Customer/Order/Tracking', [
                'order' => $order,
                'tracking_data' => $trackingData,
            ]);
        } catch (Exception $e) {
            Log::error("Order tracking exception for reference [{$reference}]: " . $e->getMessage());
            return redirect()->route('customer.order.index')->with('error', 'Pesanan tidak ditemukan.');
        }
    }

    /**
     * Show mock payment screen (simulating Xendit Invoice page).
     */
    public function showMockPayment($reference)
    {
        try {
            $order = Order::where('order_reference', $reference)->firstOrFail();

            if ($order->status_payment !== 'PENDING') {
                return redirect()->route('customer.order.tracking', $reference)
                    ->with('error', 'Pesanan ini sudah dibayar atau dibatalkan.');
            }

            return Inertia::render('Customer/Order/MockPayment', [
                'order' => $order,
            ]);
        } catch (Exception $e) {
            Log::error("Show mock payment exception for [{$reference}]: " . $e->getMessage());
            return redirect()->route('customer.order.index')->with('error', 'Tagihan tidak ditemukan.');
        }
    }

    /**
     * Perform payment simulation action (Simulate Xendit Webhook / Redirect callback).
     */
    public function payMockPayment(Request $request, $reference)
    {
        try {
            $order = Order::where('order_reference', $reference)->firstOrFail();

            if ($order->status_payment !== 'PENDING') {
                return redirect()->route('customer.order.tracking', $reference)
                    ->with('error', 'Pesanan ini sudah dibayar.');
            }

            // Update status
            $order->update([
                'status_payment' => 'PAID',
            ]);

            return redirect()->route('customer.order.tracking', $reference)
                ->with('success', 'Pembayaran Berhasil! (Simulasi Xendit sukses)');
        } catch (Exception $e) {
            Log::error("Process mock payment exception for [{$reference}]: " . $e->getMessage());
            return redirect()->route('customer.order.index')->with('error', 'Gagal memproses pembayaran simulasi.');
        }
    }
}
