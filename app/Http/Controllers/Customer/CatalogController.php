<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class CatalogController extends Controller
{
    /**
     * Display the store landing page.
     */
    public function home()
    {
        try {
            $banners = Banner::where('is_active', true)
                ->orderBy('order_position', 'asc')
                ->get();

            $newArrivals = Product::with(['category', 'variants.skus.discounts', 'galleries'])
                ->where('is_active', true)
                ->latest()
                ->take(8)
                ->get();

            // Fetch active vouchers (taken from existing database schema)
            $vouchers = \App\Models\Voucher::where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->get();

            // Fetch wishlist IDs for logged-in user
            $wishlistProductIds = [];
            if (Auth::check()) {
                $wishlistProductIds = \App\Models\Wishlist::where('user_id', Auth::id())->pluck('product_id')->toArray();
            }

            return Inertia::render('Customer/Home', [
                'banners' => $banners,
                'new_arrivals' => $newArrivals,
                'vouchers' => $vouchers,
                'wishlist_product_ids' => $wishlistProductIds,
            ]);
        } catch (Exception $e) {
            Log::error('Homepage loading exception: ' . $e->getMessage());
            return Inertia::render('Customer/Home', [
                'banners' => [],
                'new_arrivals' => [],
                'vouchers' => [],
                'wishlist_product_ids' => [],
                'error' => 'Gagal memuat katalog beranda.'
            ]);
        }
    }

    /**
     * Display catalog with search, filter, and sort options.
     */
    public function catalog(Request $request)
    {
        try {
            $search = $request->input('search');
            $categorySlug = $request->input('category');
            $minPrice = $request->input('min_price');
            $maxPrice = $request->input('max_price');

            $query = Product::with(['category', 'variants.skus.discounts'])->where('is_active', true);

            // 1. Filter by Search Query
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // 2. Filter by Category
            if ($categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            $products = $query->get();

            // Filter by Promo/Discount
            if ($request->has('promo') && $request->promo) {
                $products = $products->filter(fn($product) => $product->has_discount)->values();
            }

            // 3. Filter by SKU Price ranges (In-memory collection filter)
            if ($minPrice !== null || $maxPrice !== null) {
                $products = $products->filter(function ($product) use ($minPrice, $maxPrice) {
                    $priceRange = $product->price_range;
                    
                    if ($minPrice !== null && $priceRange['min'] < (float)$minPrice) {
                        return false;
                    }
                    if ($maxPrice !== null && $priceRange['max'] > (float)$maxPrice) {
                        return false;
                    }
                    return true;
                })->values();
            }

            return Inertia::render('Customer/Catalog', [
                'products' => $products,
                'filters' => $request->only(['search', 'category', 'min_price', 'max_price']),
            ]);
        } catch (Exception $e) {
            Log::error('Catalog loading exception: ' . $e->getMessage());
            return Inertia::render('Customer/Catalog', [
                'products' => [],
                'filters' => [],
                'error' => 'Gagal memuat produk katalog.'
            ]);
        }
    }

    /**
     * Show single product details.
     */
    public function productShow(Request $request, $slug)
    {
        try {
            $skuId = $request->query('sku_id');
            $product = Product::with(['category', 'variants.skus.discounts', 'galleries'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();

            // Check if user has wishlisted this product
            $isWishlisted = false;
            if (Auth::check()) {
                $isWishlisted = Wishlist::where('user_id', Auth::id())
                    ->where('product_id', $product->id)
                    ->exists();
            }

            return Inertia::render('Customer/Product/Show', [
                'product' => $product,
                'is_wishlisted' => $isWishlisted,
                'preselected_sku_id' => $skuId ? (int)$skuId : null,
            ]);
        } catch (Exception $e) {
            Log::error("Product view exception for slug [{$slug}]: " . $e->getMessage());
            return redirect()->route('customer.catalog')->with('error', 'Produk tidak ditemukan atau tidak aktif.');
        }
    }

    /**
     * Display wishlist page for customer.
     */
    public function wishlistIndex()
    {
        try {
            $wishlists = Wishlist::with(['product.variants.skus', 'product.category'])
                ->where('user_id', Auth::id())
                ->get()
                ->pluck('product');

            return Inertia::render('Customer/Wishlist/Index', [
                'products' => $wishlists,
            ]);
        } catch (Exception $e) {
            Log::error('Wishlist loading exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat daftar produk favorit Anda.');
        }
    }

    /**
     * Toggle wishlist item.
     */
    public function wishlistToggle($productId)
    {
        try {
            $userId = Auth::id();
            $wishlist = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                $message = 'Produk dihapus dari Wishlist.';
                $status = 'removed';
            } else {
                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);
                $message = 'Produk ditambahkan ke Wishlist.';
                $status = 'added';
            }

            return redirect()->back()->with('success', $message);
        } catch (Exception $e) {
            Log::error('Wishlist toggle exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses favorit produk.');
        }
    }

    /**
     * API: Get predictive search results.
     */
    public function searchPredictive(Request $request)
    {
        $queryStr = $request->query('query');
        if (!$queryStr || strlen($queryStr) < 3) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        try {
            $products = Product::with(['category', 'variants.skus.discounts'])
                ->where('is_active', true)
                ->where(function ($q) use ($queryStr) {
                    $q->where('name', 'like', "%{$queryStr}%")
                      ->orWhere('description', 'like', "%{$queryStr}%");
                })
                ->take(5)
                ->get();

            $formatted = $products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'cover_image' => $product->cover_image,
                    'price_range' => $product->price_range,
                    'category_name' => $product->category?->name,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formatted
            ]);
        } catch (Exception $e) {
            Log::error('Predictive search exception: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal melakukan pencarian.'
            ], 500);
        }
    }
}
