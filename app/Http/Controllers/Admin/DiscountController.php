<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class DiscountController extends Controller
{
    /**
     * Display listing of discounts.
     */
    public function index()
    {
        try {
            $discounts = Discount::latest()->get();
            return Inertia::render('Admin/Discounts/Index', [
                'discounts' => $discounts
            ]);
        } catch (Exception $e) {
            Log::error('Admin Discount index exception: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Gagal memuat daftar diskon.');
        }
    }

    /**
     * Store new discount.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'required|boolean',
        ]);

        try {
            Discount::create([
                'title' => $request->title,
                'type' => $request->type,
                'value' => $request->value,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
                'is_active' => $request->is_active,
            ]);

            return redirect()->back()->with('success', 'Diskon baru berhasil dibuat.');
        } catch (Exception $e) {
            Log::error('Admin Discount store exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat diskon baru.');
        }
    }

    /**
     * Update discount details.
     */
    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'required|boolean',
        ]);

        try {
            $discount->update([
                'title' => $request->title,
                'type' => $request->type,
                'value' => $request->value,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
                'is_active' => $request->is_active,
            ]);

            return redirect()->back()->with('success', 'Diskon berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Admin Discount update exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui diskon.');
        }
    }

    /**
     * Delete discount.
     */
    public function destroy($id)
    {
        try {
            $discount = Discount::findOrFail($id);
            $discount->delete();
            return redirect()->back()->with('success', 'Diskon berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Discount destroy exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus diskon.');
        }
    }
}
