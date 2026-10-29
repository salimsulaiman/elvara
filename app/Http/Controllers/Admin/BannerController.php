<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Exception;

class BannerController extends Controller
{
    /**
     * Display listing of banners.
     */
    public function index()
    {
        try {
            $banners = Banner::orderBy('order_position', 'asc')->get();
            return Inertia::render('Admin/Banners/Index', [
                'banners' => $banners
            ]);
        } catch (Exception $e) {
            Log::error('Admin Banner index exception: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Gagal memuat daftar banner.');
        }
    }

    /**
     * Store new banner.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image_path' => 'required|image|max:3072', // Limit to 3MB
            'link_url' => 'nullable|string|max:255',
            'order_position' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        try {
            $path = '/storage/' . $request->file('image_path')->store('images', 'public');

            Banner::create([
                'title' => $request->title,
                'image_path' => $path,
                'link_url' => $request->link_url,
                'order_position' => $request->order_position,
                'is_active' => $request->is_active,
            ]);

            return redirect()->back()->with('success', 'Banner baru berhasil diunggah.');
        } catch (Exception $e) {
            Log::error('Admin Banner store exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengunggah banner baru.');
        }
    }

    /**
     * Update banner details.
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'image_path' => 'nullable|image|max:3072',
            'link_url' => 'nullable|string|max:255',
            'order_position' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        try {
            $data = [
                'title' => $request->title,
                'link_url' => $request->link_url,
                'order_position' => $request->order_position,
                'is_active' => $request->is_active,
            ];

            if ($request->hasFile('image_path')) {
                // Delete old image file
                if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                    Storage::disk('public')->delete($banner->image_path);
                }
                
                // Store new image
                $data['image_path'] = '/storage/' . $request->file('image_path')->store('images', 'public');
            }

            $banner->update($data);

            return redirect()->back()->with('success', 'Banner berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Admin Banner update exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui banner.');
        }
    }

    /**
     * Delete banner.
     */
    public function destroy($id)
    {
        try {
            $banner = Banner::findOrFail($id);

            // Delete file from storage
            if ($banner->image_path && Storage::disk('public')->exists($banner->image_path)) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $banner->delete();
            return redirect()->back()->with('success', 'Banner berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Banner destroy exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus banner.');
        }
    }
}