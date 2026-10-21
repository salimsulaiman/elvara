<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\Voucher;
use App\Services\RajaOngkirService;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class CheckoutController extends Controller
{
    protected RajaOngkirService $rajaOngkir;
    protected XenditService $xendit;

    public function __construct(RajaOngkirService $rajaOngkir, XenditService $xendit)
    {
        $this->rajaOngkir = $rajaOngkir;
        $this->xendit = $xendit;
    }

    /**
     * Display checkout page.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $cart = Cart::with(['items.sku.variant.product', 'items.sku.discounts'])
                ->where('user_id', $user->id)
                ->firstOrFail();

            if ($cart->items->isEmpty()) {
                return redirect()->route('customer.cart.index')->with('error', 'Keranjang belanja Anda kosong.');
            }

            // Get provinces list from RajaOngkir for checkout form dropdowns
            $provinces = $this->rajaOngkir->getProvinces();

            return Inertia::render('Customer/Checkout', [
                'cart' => $cart,
                'provinces' => $provinces,
            ]);
        } catch (Exception $e) {
            Log::error('Checkout page loading exception: ' . $e->getMessage());
            return redirect()->route('customer.cart.index')->with('error', 'Terjadi kesalahan saat memuat halaman checkout.');
        }
    }

    /**
     * API: Get provinces list.
     */
    public function getProvinces()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->rajaOngkir->getProvinces()
            ]);
        } catch (Exception $e) {
            Log::error('API getProvinces exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat provinsi.'], 500);
        }
    }

    /**
     * API: Get cities by province ID.
     */
    public function getCities($provinceId)
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->rajaOngkir->getCities($provinceId)
            ]);
        } catch (Exception $e) {
            Log::error('API getCities exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat kota.'], 500);
        }
    }

    /**
     * API: Get subdistricts (kecamatan) by city ID.
     */
    public function getSubdistricts($cityId)
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->rajaOngkir->getSubdistricts($cityId)
            ]);
        } catch (Exception $e) {
            Log::error('API getSubdistricts exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memuat kecamatan.'], 500);
        }
    }

    /**
     * API: Calculate shipping costs.
     */
    public function calculateShipping(Request $request)
    {
        $request->validate([
            'destination_id' => 'required',
            'weight' => 'required|integer',
            'courier' => 'required|string|in:jne,pos,j&t',
        ]);

        try {
            $costs = $this->rajaOngkir->calculateCost(
                $request->destination_id,
                $request->weight,
                $request->courier
            );

            return response()->json([
                'status' => 'success',
                'data' => $costs
            ]);
        } catch (Exception $e) {
            Log::error('API calculateShipping exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menghitung ongkos kirim.'], 500);
        }
    }

    /**
     * API: Verify and apply voucher code.
     */
    public function applyVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric',
        ]);

        try {
            $voucher = Voucher::where('code', $request->code)->first();

            if (!$voucher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode voucher tidak ditemukan.'
                ], 404);
            }

            if (!$voucher->isValidFor(Auth::user(), $request->subtotal)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Voucher tidak dapat digunakan (syarat minimum order tidak terpenuhi atau kuota habis).'
                ], 422);
            }

            // Calculate discount amount
            $discount = 0;
            if ($voucher->type === 'fixed') {
                $discount = $voucher->reward_value;
            } elseif ($voucher->type === 'percentage') {
                $discount = ($voucher->reward_value / 100) * $request->subtotal;
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'code' => $voucher->code,
                    'type' => $voucher->type,
                    'reward_value' => $voucher->reward_value,
                    'discount_amount' => $discount,
                ]
            ]);
        } catch (Exception $e) {
            Log::error('API applyVoucher exception: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal memproses voucher.'], 500);
        }
    }

    /**
     * Store and process order checkout.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string',
            'province' => 'required|string',
            'city' => 'required|string',
            'subdistrict' => 'required|string',
            'subdistrict_id' => 'required|string',
            'courier' => 'required|string|in:jne,pos,j&t',
            'service' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'voucher_code' => 'nullable|string',
        ]);

        try {
            $user = Auth::user();
            $cart = Cart::with(['items.sku.variant.product', 'items.sku.discounts'])->where('user_id', $user->id)->firstOrFail();

            if ($cart->items->isEmpty()) {
                return redirect()->route('customer.cart.index')->with('error', 'Keranjang belanja Anda kosong.');
            }

            // Calculate Subtotal
            $subtotal = 0;
            foreach ($cart->items as $item) {
                $subtotal += $item->sku->discounted_price * $item->quantity;
            }

            // Verify and Calculate Voucher Discount
            $discount = 0;
            $voucher = null;
            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)->first();
                if ($voucher && $voucher->isValidFor($user, $subtotal)) {
                    if ($voucher->type === 'fixed') {
                        $discount = $voucher->reward_value;
                    } elseif ($voucher->type === 'percentage') {
                        $discount = ($voucher->reward_value / 100) * $subtotal;
                    }
                }
            }

            $shippingCost = $request->shipping_cost;
            $grandTotal = ($subtotal - $discount) + $shippingCost;

            if ($grandTotal < 0) {
                $grandTotal = 0;
            }

            // Format address string
            $fullAddress = "Nama: {$request->recipient_name} | Telp: {$request->recipient_phone}\n" .
                           "{$request->recipient_address}, Kec. {$request->subdistrict}, {$request->city}, Prov. {$request->province}";

            $order = null;

            // Database Transaction to guarantee atomic operations
            DB::transaction(function () use ($user, $subtotal, $discount, $shippingCost, $grandTotal, $request, $fullAddress, $cart, $voucher, &$order) {
                
                // 1. Create Order
                $order = Order::create([
                    'user_id' => $user->id,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discount,
                    'shipping_cost' => $shippingCost,
                    'grand_total' => $grandTotal,
                    'status_payment' => 'PENDING',
                    'status_delivery' => 'UNSHIPPED',
                    'shipping_courier' => strtoupper($request->courier),
                    'shipping_service' => $request->service,
                    'shipping_address' => $fullAddress,
                    'rajaongkir_destination_id' => $request->subdistrict_id,
                ]);

                // 2. Loop Items, Check Stock, Deduct Stock, and Save OrderItems
                foreach ($cart->items as $item) {
                    $sku = ProductSku::lockForUpdate()->findOrFail($item->product_sku_id);

                    if ($sku->stock < $item->quantity) {
                        throw new Exception("Stok untuk produk {$sku->variant->product->name} (Ukuran: {$sku->size}) tidak cukup.");
                    }

                    // Deduct stock
                    $sku->decrement('stock', $item->quantity);

                    // Create item record
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_sku_id' => $sku->id,
                        'quantity' => $item->quantity,
                        'price_at_purchase' => $sku->discounted_price,
                    ]);
                }

                // 3. Increment voucher usage
                if ($voucher) {
                    $voucher->increment('global_used');
                    $user->usedVouchers()->attach($voucher->id, [
                        'order_id' => $order->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // 4. Clear User Cart Items
                $cart->items()->delete();
            });

            // 5. Generate Payment link from Xendit Service (mock / real)
            $paymentUrl = $this->xendit->createInvoice($order);
            
            // Save invoice link
            $order->update(['xendit_invoice_url' => $paymentUrl]);

            return redirect()->route('customer.order.tracking', $order->order_reference)
                ->with('success', 'Pesanan Anda berhasil dibuat. Silakan selesaikan pembayaran.');

        } catch (Exception $e) {
            Log::error('Order creation exception: ' . $e->getMessage());
            return redirect()->route('customer.checkout.index')->with('error', $e->getMessage() ?: 'Gagal memproses pesanan Anda. Silakan coba kembali.');
        }
    }
}
