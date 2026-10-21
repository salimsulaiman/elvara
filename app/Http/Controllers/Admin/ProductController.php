<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSku;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Exception;

class ProductController extends Controller
{
    /**
     * Display listing of products.
     */
    public function index()
    {
        try {
            $products = Product::with(['category', 'variants.skus'])->latest()->get();
            return Inertia::render('Admin/Products/Index', [
                'products' => $products
            ]);
        } catch (Exception $e) {
            Log::error('Admin Product index exception: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Gagal memuat daftar produk.');
        }
    }

    /**
     * Show form to create product.
     */
    public function create()
    {
        try {
            $categories = Category::select('id', 'name')->get();
            return Inertia::render('Admin/Products/Create', [
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            Log::error('Admin Product create form exception: ' . $e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'Gagal memuat form pembuatan produk.');
        }
    }

    /**
     * Store new product master record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name',
            'description' => 'required|string',
            'weight' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        try {
            $product = Product::create([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'weight' => $request->weight,
                'is_active' => $request->is_active,
            ]);

            return redirect()->route('admin.products.edit', $product->id)
                ->with('success', 'Produk master berhasil dibuat. Silakan tambahkan Varian & SKU Matrix.');
        } catch (Exception $e) {
            Log::error('Admin Product store exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat produk master.');
        }
    }

    /**
     * Show edit product layout (includes Variant & SKU Matrix management).
     */
    public function edit($id)
    {
        try {
            $product = Product::with(['variants.skus.discounts', 'galleries'])->findOrFail($id);
            $categories = Category::select('id', 'name')->get();
            $discounts = \App\Models\Discount::where('is_active', true)->get();

            return Inertia::render('Admin/Products/Create', [
                'product' => $product,
                'categories' => $categories,
                'discounts' => $discounts,
                'is_editing' => true
            ]);
        } catch (Exception $e) {
            Log::error('Admin Product edit view exception: ' . $e->getMessage());
            return redirect()->route('admin.products.index')->with('error', 'Produk tidak ditemukan.');
        }
    }

    /**
     * Update product details.
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'description' => 'required|string',
            'weight' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
        ]);

        try {
            $product->update([
                'category_id' => $request->category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'weight' => $request->weight,
                'is_active' => $request->is_active,
            ]);

            return redirect()->route('admin.products.index')->with('success', 'Produk master berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Admin Product update exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui produk master.');
        }
    }

    /**
     * Delete product.
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Delete associated variant images
            foreach ($product->variants as $variant) {
                if ($variant->variant_image && Storage::disk('public')->exists($variant->variant_image)) {
                    Storage::disk('public')->delete($variant->variant_image);
                }
            }

            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Produk beserta seluruh varian & SKU berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Product destroy exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus produk.');
        }
    }

    /**
     * Store a new color variant.
     */
    public function storeVariant(Request $request, Product $product)
    {
        $request->validate([
            'variant_name' => 'required|string|max:100',
            'variant_image' => 'nullable|image|max:2048', // Allow images up to 2MB
        ]);

        try {
            $imagePath = null;
            if ($request->hasFile('variant_image')) {
                $imagePath = '/' . $request->file('variant_image')->store('images/products', 'public');
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'variant_name' => $request->variant_name,
                'variant_image' => $imagePath,
            ]);

            return redirect()->back()->with('success', 'Varian warna baru berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Admin Product storeVariant exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan varian warna.');
        }
    }

    /**
     * Delete a color variant.
     */
    public function destroyVariant(ProductVariant $variant)
    {
        try {
            if ($variant->variant_image && Storage::disk('public')->exists($variant->variant_image)) {
                Storage::disk('public')->delete($variant->variant_image);
            }

            $variant->delete();
            return redirect()->back()->with('success', 'Varian warna beserta ukuran SKU terkait berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Product destroyVariant exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus varian warna.');
        }
    }

    /**
     * Store SKU for variant (size, price, stock).
     */
    public function storeSku(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'size' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        try {
            // Check if SKU already exists for this variant size
            $existing = ProductSku::where('product_variant_id', $variant->id)
                ->where('size', $request->size)
                ->first();

            if ($existing) {
                return redirect()->back()->with('error', 'Ukuran SKU untuk varian warna ini sudah terdaftar.');
            }

            // Generate unique SKU code
            $skuCode = 'ELV-' . strtoupper(substr($variant->product->name, 0, 3)) . '-' .
                        strtoupper(substr($variant->variant_name, 0, 3)) . '-' .
                        strtoupper(str_replace(' ', '', $request->size)) . '-' .
                        rand(100, 999);

            ProductSku::create([
                'product_variant_id' => $variant->id,
                'sku' => $skuCode,
                'size' => $request->size,
                'price' => $request->price,
                'stock' => $request->stock,
            ]);

            return redirect()->back()->with('success', 'SKU ukuran berhasil ditambahkan.');
        } catch (Exception $e) {
            Log::error('Admin Product storeSku exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menambahkan ukuran SKU.');
        }
    }

    /**
     * Delete SKU size.
     */
    public function destroySku(ProductSku $sku)
    {
        try {
            $sku->delete();
            return redirect()->back()->with('success', 'Ukuran SKU berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Product destroySku exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus ukuran SKU.');
        }
    }

    /**
     * Update SKU discount.
     */
    public function updateSkuDiscount(Request $request, ProductSku $sku)
    {
        $request->validate([
            'discount_id' => 'nullable|exists:discounts,id',
        ]);

        try {
            if ($request->discount_id) {
                $sku->discounts()->sync([$request->discount_id]);
            } else {
                $sku->discounts()->detach();
            }

            return redirect()->back()->with('success', 'Diskon SKU berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Admin Product updateSkuDiscount exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui diskon SKU.');
        }
    }
}
