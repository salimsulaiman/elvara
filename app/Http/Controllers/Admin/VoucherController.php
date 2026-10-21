<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Exception;

class VoucherController extends Controller
{
    /**
     * Display listing of vouchers.
     */
    public function index()
    {
        try {
            $vouchers = Voucher::latest()->get();
            return Inertia::render('Admin/Vouchers/Index', [
                'vouchers' => $vouchers
            ]);
        } catch (Exception $e) {
            Log::error('Admin Voucher index exception: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Gagal memuat daftar voucher.');
        }
    }

    /**
     * Store new voucher.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code',
            'type' => 'required|in:fixed,percentage',
            'reward_value' => 'required|numeric|min:1',
            'min_order_amount' => 'required|numeric|min:0',
            'global_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        try {
            Voucher::create([
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'reward_value' => $request->reward_value,
                'min_order_amount' => $request->min_order_amount,
                'global_limit' => $request->global_limit,
                'per_user_limit' => $request->per_user_limit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active,
            ]);

            return redirect()->back()->with('success', 'Voucher baru berhasil dibuat.');
        } catch (Exception $e) {
            Log::error('Admin Voucher store exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat voucher baru.');
        }
    }

    /**
     * Update voucher details.
     */
    public function update(Request $request, $id)
    {
        $voucher = Voucher::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:vouchers,code,' . $voucher->id,
            'type' => 'required|in:fixed,percentage',
            'reward_value' => 'required|numeric|min:1',
            'min_order_amount' => 'required|numeric|min:0',
            'global_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
        ]);

        try {
            $voucher->update([
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'reward_value' => $request->reward_value,
                'min_order_amount' => $request->min_order_amount,
                'global_limit' => $request->global_limit,
                'per_user_limit' => $request->per_user_limit,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active,
            ]);

            return redirect()->back()->with('success', 'Voucher berhasil diperbarui.');
        } catch (Exception $e) {
            Log::error('Admin Voucher update exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui voucher.');
        }
    }

    /**
     * Delete voucher.
     */
    public function destroy($id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->delete();
            return redirect()->back()->with('success', 'Voucher berhasil dihapus.');
        } catch (Exception $e) {
            Log::error('Admin Voucher destroy exception: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus voucher.');
        }
    }
}
