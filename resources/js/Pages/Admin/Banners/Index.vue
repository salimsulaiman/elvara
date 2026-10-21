<template>
    <AdminLayout>
        <div class="space-y-8 font-sans">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4">
                <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">Banner Promo Homepage</h1>
                <p class="text-xs text-gray-dark mt-1">Kelola banner promosi lebar (widescreen) yang tampil pada carousel halaman depan.</p>
            </div>

            <!-- Content Grid split: Form & Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left: Create/Update Form -->
                <div class="content-card space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        {{ isEditing ? 'Edit Banner' : 'Unggah Banner Baru' }}
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Judul / Teks Banner</label>
                            <input type="text" v-model="form.title" placeholder="Contoh: ELVARA NEW SEASON" class="input-field text-xs h-9 py-1" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Berkas Gambar (Rasio 16:9)</label>
                            <input type="file" @change="handleImageFile" accept="image/*" class="text-xs text-gray-dark file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-ink-dark file:text-white hover:file:opacity-90 cursor-pointer" />
                            <p v-if="isEditing" class="text-[9px] text-gray-dark font-medium italic mt-0.5">Biarkan kosong jika tidak ingin mengubah gambar.</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Tautan URL Klik (Link)</label>
                            <input type="text" v-model="form.link_url" placeholder="/catalog atau url eksternal" class="input-field text-xs h-9 py-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Urutan Posisi</label>
                                <input type="number" v-model="form.order_position" required min="0" class="input-field h-9" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Status Tampil</label>
                                <select v-model="form.is_active" required class="input-field py-0 h-9">
                                    <option :value="1">Tampilkan</option>
                                    <option :value="0">Sembunyikan</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="formProcessing" class="btn-primary py-2.5 text-xs w-full">
                                {{ isEditing ? 'Simpan Banner' : 'Unggah Banner' }}
                            </button>
                            <button v-if="isEditing" type="button" @click="cancelEdit" class="btn-secondary py-2.5 text-xs w-full">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Banners List Table -->
                <div class="content-card lg:col-span-2 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Daftar Banner Promo Homepage
                    </h3>

                    <div v-if="banners.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-border-soft text-gray-dark font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-3 pr-4">Gambar Preview</th>
                                    <th class="py-3 pr-4">Judul Banner</th>
                                    <th class="py-3 pr-4">Tautan Link</th>
                                    <th class="py-3 pr-4">Posisi</th>
                                    <th class="py-3 pr-4">Status</th>
                                    <th class="py-3 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="b in banners" :key="b.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                    <td class="py-3 pr-4">
                                        <div class="w-20 h-10 rounded border border-border-soft bg-gray-light overflow-hidden flex-shrink-0">
                                            <img :src="b.image_path" class="w-full h-full object-cover" />
                                        </div>
                                    </td>
                                    <td class="py-3 pr-4 font-bold text-ink-dark">{{ b.title || '(Tanpa Teks)' }}</td>
                                    <td class="py-3 pr-4 text-gray-dark font-mono truncate max-w-[120px]">{{ b.link_url || '-' }}</td>
                                    <td class="py-3 pr-4 text-gray-dark">{{ b.order_position }}</td>
                                    <td class="py-3 pr-4">
                                        <span :class="[b.is_active ? 'text-ink-dark font-bold' : 'text-gray-dark italic opacity-70', 'text-[10px] uppercase']">
                                            {{ b.is_active ? 'TAMPIL' : 'SEMBUNYI' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right space-x-2 whitespace-nowrap">
                                        <button @click="editBanner(b)" class="text-ink-dark hover:underline font-bold cursor-pointer">
                                            Edit
                                        </button>
                                        <button @click="deleteBanner(b)" class="text-gray-dark hover:text-ink-dark hover:underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-dark">
                        Belum ada banner promo dibuat.
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
    banners: {
        type: Array,
        default: () => []
    }
});

const isEditing = ref(false);
const editingId = ref(null);
const formProcessing = ref(false);

const form = reactive({
    title: '',
    image_path: null,
    link_url: '',
    order_position: 1,
    is_active: 1
});

const handleImageFile = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image_path = file;
    }
};

const submitForm = () => {
    formProcessing.value = true;
    
    const formData = new FormData();
    formData.append('title', form.title || '');
    formData.append('link_url', form.link_url || '');
    formData.append('order_position', form.order_position);
    formData.append('is_active', form.is_active);

    if (form.image_path) {
        formData.append('image_path', form.image_path);
    }

    if (isEditing.value) {
        // Since Laravel doesn't support file uploads via PUT/PATCH out of the box with FormData,
        // we can simulate it using POST and appending '_method = PUT'
        formData.append('_method', 'PUT');

        router.post(`/admin/banners/${editingId.value}`, formData, {
            onSuccess: () => {
                cancelEdit();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    } else {
        router.post('/admin/banners', formData, {
            onSuccess: () => {
                resetForm();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    }
};

const editBanner = (b) => {
    isEditing.value = true;
    editingId.value = b.id;
    form.title = b.title || '';
    form.link_url = b.link_url || '';
    form.order_position = b.order_position;
    form.is_active = b.is_active ? 1 : 0;
    form.image_path = null; // Reset file input
};

const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    resetForm();
};

const resetForm = () => {
    form.title = '';
    form.image_path = null;
    form.link_url = '';
    form.order_position = 1;
    form.is_active = 1;
};

const deleteBanner = (b) => {
    if (confirm(`Apakah Anda yakin ingin menghapus banner "${b.title || '(Tanpa Teks)'}"?`)) {
        router.delete(`/admin/banners/${b.id}`);
    }
};
</script>
