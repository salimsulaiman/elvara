<template>
    <AdminLayout>
        <div class="space-y-8 font-sans">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4">
                <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">Manajemen Kategori</h1>
                <p class="text-xs text-gray-dark mt-1">Kelola pembagian kategori produk katalog toko Anda secara dinamis.</p>
            </div>

            <!-- Content Grid split: Form & Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left: Create/Update Form -->
                <div class="content-card space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        {{ isEditing ? 'Edit Kategori' : 'Buat Kategori Baru' }}
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nama Kategori</label>
                            <input type="text" v-model="form.name" required placeholder="Contoh: Pakaian Baru" class="input-field" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Deskripsi Singkat</label>
                            <textarea v-model="form.description" class="input-field h-24 py-3 resize-none" placeholder="Tulis deskripsi kategori..."></textarea>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button type="submit" :disabled="formProcessing" class="btn-primary py-2 text-xs w-full">
                                {{ isEditing ? 'Simpan Perubahan' : 'Buat Kategori' }}
                            </button>
                            <button v-if="isEditing" type="button" @click="cancelEdit" class="btn-secondary py-2 text-xs w-full">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Categories List Table -->
                <div class="content-card lg:col-span-2 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Daftar Kategori Aktif
                    </h3>

                    <div v-if="categories.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-border-soft text-gray-dark font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-3 pr-4">Nama</th>
                                    <th class="py-3 pr-4">Slug</th>
                                    <th class="py-3 pr-4">Deskripsi</th>
                                    <th class="py-3 pr-4">Jumlah Produk</th>
                                    <th class="py-3 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="cat in categories" :key="cat.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                    <td class="py-3 pr-4 font-bold text-ink-dark">{{ cat.name }}</td>
                                    <td class="py-3 pr-4 text-gray-dark font-mono">{{ cat.slug }}</td>
                                    <td class="py-3 pr-4 text-gray-dark truncate max-w-[150px]">{{ cat.description || '-' }}</td>
                                    <td class="py-3 pr-4 text-gray-dark">{{ cat.products_count || 0 }} produk</td>
                                    <td class="py-3 text-right space-x-2 whitespace-nowrap">
                                        <button @click="editCategory(cat)" class="text-ink-dark hover:underline font-bold cursor-pointer">
                                            Edit
                                        </button>
                                        <button @click="deleteCategory(cat)" class="text-gray-dark hover:text-ink-dark hover:underline cursor-pointer">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-dark">
                        Belum ada kategori terdaftar.
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
    categories: {
        type: Array,
        default: () => []
    }
});

const isEditing = ref(false);
const editingId = ref(null);
const formProcessing = ref(false);

const form = reactive({
    name: '',
    description: ''
});

const submitForm = () => {
    formProcessing.value = true;
    
    if (isEditing.value) {
        // Update
        router.put(`/admin/categories/${editingId.value}`, form, {
            onSuccess: () => {
                cancelEdit();
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    } else {
        // Create
        router.post('/admin/categories', form, {
            onSuccess: () => {
                form.name = '';
                form.description = '';
            },
            onFinish: () => {
                formProcessing.value = false;
            }
        });
    }
};

const editCategory = (cat) => {
    isEditing.value = true;
    editingId.value = cat.id;
    form.name = cat.name;
    form.description = cat.description;
};

const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    form.name = '';
    form.description = '';
};

const deleteCategory = (cat) => {
    if (cat.products_count > 0) {
        alert('Kategori tidak dapat dihapus karena memiliki produk aktif di dalamnya.');
        return;
    }
    
    if (confirm(`Apakah Anda yakin ingin menghapus kategori "${cat.name}"?`)) {
        router.delete(`/admin/categories/${cat.id}`);
    }
};
</script>
