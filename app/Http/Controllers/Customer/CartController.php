<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class CartController extends Controller
{
    /**
     * Display customer cart items.
     */
    public function index()
    {
        try {
            $cart = Cart::with(['items.sku.variant.product.category', 'items.sku.discounts'])
                ->firstOrCreate(['user_id' => Auth::id()]);

            return Inertia::render('Customer/Cart', [
                'cart' => $cart,
            ]);
        } catch (Exception $e) {
            Log::error('Cart loading exception: ' . $e->getMessage());
            return redirect()->route('customer.home')->with('error', 'Gagal memuat keranjang belanja Anda.');
        }
    }

    /**
     * Add a product variant SKU to the cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_sku_id' => 'required|exists:product_skus,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $sku = ProductSku::findOrFail($request->product_sku_id);
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);

            // Check if SKU stock is sufficient
            if ($sku->stock < $request->quantity) {
                return redirect()->back()->with('error', "Stok tidak mencukupi. Hanya tersedia {$sku->stock} item.");
            }

            // Check if item already exists in cart
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_sku_id', $sku->id)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $request->quantity;
                if ($sku->stock < $newQuantity) {
                    return redirect()->back()->with('error', "Gagal menambah jumlah. Stok total hanya tersedia {$sku->stock} item.");
                }
                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_sku_id' => $sku->id,
                    'quantity' => $request->quantity,
                ]);
            }

            return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
        } catch (Exception $e) {
            Log::error('Cart store exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan produk ke keranjang.');
        }
    }

    /**
     * Update the quantity of a cart item.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartItem = CartItem::findOrFail($id);
            
            // Security check: ensure cart item belongs to logged-in user
            if ($cartItem->cart->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Aksi tidak diizinkan.');
            }

            $sku = $cartItem->sku;

            // Check stock
            if ($sku->stock < $request->quantity) {
                return redirect()->back()->with('error', "Stok tidak mencukupi. Maksimal pembelian adalah {$sku->stock} item.");
            }

            $cartItem->update(['quantity' => $request->quantity]);

            return redirect()->back()->with('success', 'Jumlah produk diperbarui.');
        } catch (Exception $e) {
            Log::error('Cart update exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui jumlah produk.');
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy($id)
    {
        try {
            $cartItem = CartItem::findOrFail($id);

            // Security check: ensure cart item belongs to logged-in user
            if ($cartItem->cart->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'Aksi tidak diizinkan.');
            }

            $cartItem->delete();

            return redirect()->back()->with('success', 'Produk dihapus dari keranjang.');
        } catch (Exception $e) {
            Log::error('Cart destroy exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengeluarkan produk dari keranjang.');
        }
    }
}
