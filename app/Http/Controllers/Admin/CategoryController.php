<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class CategoryController extends Controller
{
    /**
     * Display listing of categories.
     */
    public function index()
    {
        try {
            $categories = Category::withCount('products')->get();
            return Inertia::render('Admin/Categories/Index', [
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            Log::error('Admin Category index exception: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Gagal memuat daftar kategori.');
        }
    }

    /**
     * Store new category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        try {
            Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Kategori baru berhasil dibuat.');
        } catch (Exception $e) {
            Log::error('Admin Category store exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat kategori baru.');
        }
    }

    /**
     * Update category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        try {
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
            ]);

            return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Admin Category update exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui kategori.');
        }
    }

    /**
     * Remove category.
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category has products
            if ($category->products()->exists()) {
                return redirect()->back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk aktif.');
            }

            $category->delete();
            return redirect()->back()->with('success', 'Kategori berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Category destroy exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kategori.');
        }
    }
}
