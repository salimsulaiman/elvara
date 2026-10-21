<template>
    <AdminLayout>
        <div class="space-y-8 font-sans">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4">
                <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">Kupon Promo & Voucher</h1>
                <p class="text-xs text-gray-dark mt-1">Kelola pembuatan kode voucher diskon belanja untuk promosi toko.</p>
            </div>

            <!-- Content Grid split: Form & Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left: Create/Update Form -->
                <div class="content-card space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        {{ isEditing ? 'Edit Voucher' : 'Buat Voucher Baru' }}
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Kode Voucher (Kapital)</label>
                            <input type="text" v-model="form.code" required placeholder="Contoh: ELVARANEW" class="input-field uppercase" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Tipe Diskon</label>
                                <select v-model="form.type" required class="input-field py-0">
                                    <option value="fixed">Potongan Flat (Rp)</option>
                                    <option value="percentage">Persentase (%)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nilai Potongan</label>
                                <input type="number" v-model="form.reward_value" required min="1" class="input-field" />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Minimal Belanja (Rp)</label>
                            <input type="number" v-model="form.min_order_amount" required min="0" class="input-field" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Kuota Global</label>
                                <input type="number" v-model="form.global_limit" placeholder="Tak Terbatas" class="input-field" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Batas per User</label>
                                <input type="number" v-model="form.per_user_limit" required min="1" class="input-field" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Mulai Berlaku</label>
                                <input type="date" v-model="form.start_date" class="input-field py-1" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Selesai Berlaku</label>
                                <input type="date" v-model="form.end_date" class="input-field py-1" />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Status Aktif</label>
                            <select v-model="form.is_active" required class="input-field py-0">
                                <option :value="1">Aktif</option>
                                <option :value="0">Nonaktif</option>
                            </select>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="formProcessing" class="btn-primary py-2.5 text-xs w-full">
                                {{ isEditing ? 'Simpan Perubahan' : 'Buat Voucher' }}
                            </button>
                            <button v-if="isEditing" type="button" @click="cancelEdit" class="btn-secondary py-2.5 text-xs w-full">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Vouchers List Table -->
                <div class="content-card lg:col-span-2 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Daftar Voucher Aktif & Kupon Promo
                    </h3>

                    <div v-if="vouchers.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-border-soft text-gray-dark font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-3 pr-4">Kode</th>
                                    <th class="py-3 pr-4">Potongan</th>
                                    <th class="py-3 pr-4">Min Order</th>
                                    <th class="py-3 pr-4">Penggunaan</th>
                                    <th class="py-3 pr-4">Status</th>
                                    <th class="py-3 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="v in vouchers" :key="v.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                    <td class="py-3 pr-4 font-bold text-ink-dark font-mono">{{ v.code }}</td>
                                    <td class="py-3 pr-4 text-gray-dark font-semibold">
                                        {{ v.type === 'fixed' ? formatPrice(v.reward_value) : `${v.reward_value}%` }}
                                    </td>
                                    <td class="py-3 pr-4 text-gray-dark">{{ formatPrice(v.min_order_amount) }}</td>
                                    <td class="py-3 pr-4 text-gray-dark font-medium">
                                        {{ v.global_used }} / {{ v.global_limit || '∞' }} kali (max {{ v.per_user_limit }}x/user)
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span :class="[v.is_active ? 'text-ink-dark font-bold' : 'text-gray-dark italic opacity-70', 'text-[10px] uppercase']">
                                            {{ v.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right space-x-2 whitespace-nowrap">
                                        <button @click="editVoucher(v)" class="text-ink-dark hover:underline font-bold cursor-pointer">
                                            Edit
                                        </button>
                                        <button @click="deleteVoucher(v)" class="text-gray-dark hover:text-ink-dark hover:underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-dark">
                        Belum ada voucher dibuat.
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    vouchers: {
        type: Array,
        default: () => []
    }
});

const isEditing = ref(false);
const editingId = ref(null);
const formProcessing = ref(false);

const form = reactive({
    code: '',
    type: 'fixed',
    reward_value: 0,
    min_order_amount: 0,
    global_limit: '',
    per_user_limit: 1,
    start_date: '',
    end_date: '',
    is_active: 1
});

const submitForm = () => {
    formProcessing.value = true;
    
    // Cleanup global limit input (empty string to null)
    const data = { ...form };
    if (data.global_limit === '') {
        data.global_limit = null;
    }

    if (isEditing.value) {
        router.put(`/admin/vouchers/${editingId.value}`, data, {
            onSuccess: () => {
                cancelEdit();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    } else {
        router.post('/admin/vouchers', data, {
            onSuccess: () => {
                resetForm();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    }
};

const editVoucher = (v) => {
    isEditing.value = true;
    editingId.value = v.id;
    form.code = v.code;
    form.type = v.type;
    form.reward_value = v.reward_value;
    form.min_order_amount = v.min_order_amount;
    form.global_limit = v.global_limit || '';
    form.per_user_limit = v.per_user_limit;
    
    // Format dates to YYYY-MM-DD
    form.start_date = v.start_date ? v.start_date.split('T')[0] : '';
    form.end_date = v.end_date ? v.end_date.split('T')[0] : '';
    
    form.is_active = v.is_active ? 1 : 0;
};

const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
};

const resetForm = () => {
    form.code = '';
    form.type = 'fixed';
    form.reward_value = 0;
    form.min_order_amount = 0;
    form.global_limit = '';
    form.per_user_limit = 1;
    form.start_date = '';
    form.end_date = '';
    form.is_active = 1;
};

const deleteVoucher = (v) => {
    if (confirm(`Apakah Anda yakin ingin menghapus voucher "${v.code}"?`)) {
        router.delete(`/admin/vouchers/${v.id}`);
    }
};

const formatPrice = (value) => {
    if (!value) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>
