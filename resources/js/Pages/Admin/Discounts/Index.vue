<template>
    <AdminLayout>
        <div class="space-y-8 font-sans">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4">
                <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">Event & Diskon Produk</h1>
                <p class="text-xs text-gray-dark mt-1">Kelola pembuatan potongan harga langsung (Diskon) berdasarkan periode waktu tertentu.</p>
            </div>

            <!-- Content Grid split: Form & Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left: Create/Update Form -->
                <div class="content-card space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        {{ isEditing ? 'Edit Event Diskon' : 'Buat Diskon Baru' }}
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nama Event / Deskripsi Diskon</label>
                            <input type="text" v-model="form.title" required placeholder="Contoh: Diskon Akhir Tahun" class="input-field" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Tipe Diskon</label>
                                <select v-model="form.type" required class="input-field py-0">
                                    <option value="percentage">Persentase (%)</option>
                                    <option value="fixed">Potongan Flat (Rp)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nilai Diskon</label>
                                <input type="number" v-model="form.value" required min="1" class="input-field" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Mulai Berlaku</label>
                                <input type="date" v-model="form.starts_at" class="input-field py-1" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Selesai Berlaku</label>
                                <input type="date" v-model="form.ends_at" class="input-field py-1" />
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
                                {{ isEditing ? 'Simpan Perubahan' : 'Buat Diskon' }}
                            </button>
                            <button v-if="isEditing" type="button" @click="cancelEdit" class="btn-secondary py-2.5 text-xs w-full">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Discounts List Table -->
                <div class="content-card lg:col-span-2 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Daftar Event Diskon Terdaftar
                    </h3>

                    <div v-if="discounts.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-border-soft text-gray-dark font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-3 pr-4">Nama Event</th>
                                    <th class="py-3 pr-4">Potongan</th>
                                    <th class="py-3 pr-4">Mulai Berlaku</th>
                                    <th class="py-3 pr-4">Selesai Berlaku</th>
                                    <th class="py-3 pr-4">Status</th>
                                    <th class="py-3 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="d in discounts" :key="d.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                    <td class="py-3 pr-4 font-bold text-ink-dark">{{ d.title }}</td>
                                    <td class="py-3 pr-4 text-gray-dark font-semibold">
                                        {{ d.type === 'fixed' ? formatPrice(d.value) : `${d.value}%` }}
                                    </td>
                                    <td class="py-3 pr-4 text-gray-dark">{{ formatDate(d.starts_at) }}</td>
                                    <td class="py-3 pr-4 text-gray-dark">{{ formatDate(d.ends_at) }}</td>
                                    <td class="py-3 pr-4">
                                        <span :class="[d.is_active ? 'text-ink-dark font-bold' : 'text-gray-dark italic opacity-70', 'text-[10px] uppercase']">
                                            {{ d.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right space-x-2 whitespace-nowrap">
                                        <button @click="editDiscount(d)" class="text-ink-dark hover:underline font-bold cursor-pointer">
                                            Edit
                                        </button>
                                        <button @click="deleteDiscount(d)" class="text-gray-dark hover:text-ink-dark hover:underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-dark">
                        Belum ada event diskon dibuat.
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
    discounts: {
        type: Array,
        default: () => []
    }
});

const isEditing = ref(false);
const editingId = ref(null);
const formProcessing = ref(false);

const form = reactive({
    title: '',
    type: 'percentage',
    value: 0,
    starts_at: '',
    ends_at: '',
    is_active: 1
});

const submitForm = () => {
    formProcessing.value = true;
    
    // Cleanup dates (empty string to null)
    const data = { ...form };
    if (data.starts_at === '') data.starts_at = null;
    if (data.ends_at === '') data.ends_at = null;

    if (isEditing.value) {
        router.put(`/admin/discounts/${editingId.value}`, data, {
            onSuccess: () => {
                cancelEdit();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    } else {
        router.post('/admin/discounts', data, {
            onSuccess: () => {
                resetForm();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    }
};

const editDiscount = (d) => {
    isEditing.value = true;
    editingId.value = d.id;
    form.title = d.title;
    form.type = d.type;
    form.value = d.value;
    
    // Format dates to YYYY-MM-DD
    form.starts_at = d.starts_at ? d.starts_at.split('T')[0] : '';
    form.ends_at = d.ends_at ? d.ends_at.split('T')[0] : '';
    
    form.is_active = d.is_active ? 1 : 0;
};

const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
};

const resetForm = () => {
    form.title = '';
    form.type = 'percentage';
    form.value = 0;
    form.starts_at = '';
    form.ends_at = '';
    form.is_active = 1;
};

const deleteDiscount = (d) => {
    if (confirm(`Apakah Anda yakin ingin menghapus event diskon "${d.title}"?`)) {
        router.delete(`/admin/discounts/${d.id}`);
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

const formatDate = (dateStr) => {
    if (!dateStr) return 'Selamanya';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(date);
};
</script>
