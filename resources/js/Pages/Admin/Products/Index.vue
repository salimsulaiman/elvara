<template>
    <AdminLayout>
        <div class="space-y-8 font-sans">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">Master Katalog Produk</h1>
                    <p class="text-xs text-gray-dark mt-1">Kelola master data produk utama, variasi warna, serta ukuran SKU matrix Anda.</p>
                </div>
                <Link href="/admin/products/create" class="btn-primary py-2 px-4 text-xs font-semibold whitespace-nowrap h-fit">
                    + Tambah Produk Baru
                </Link>
            </div>

            <!-- Products List Table -->
            <div class="content-card space-y-4">
                <div v-if="products.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-border-soft text-gray-dark font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 pr-4">Cover</th>
                                <th class="py-3 pr-4">Nama Produk</th>
                                <th class="py-3 pr-4">Kategori</th>
                                <th class="py-3 pr-4">Rentang Harga SKU</th>
                                <th class="py-3 pr-4">Total Stok</th>
                                <th class="py-3 pr-4">Status</th>
                                <th class="py-3 text-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products" :key="product.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                <td class="py-3 pr-4">
                                    <div class="w-10 h-10 rounded bg-gray-light overflow-hidden border border-border-soft flex-shrink-0">
                                        <img :src="product.cover_image" class="w-full h-full object-cover" />
                                    </div>
                                </td>
                                <td class="py-3 pr-4">
                                    <p class="font-bold text-ink-dark">{{ product.name }}</p>
                                    <p class="text-[10px] text-gray-dark font-mono mt-0.5">{{ product.slug }}</p>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="text-xs text-gray-dark font-semibold">
                                        {{ product.category?.name }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4 font-semibold text-ink-dark">
                                    {{ formatPriceSpread(product.price_range) }}
                                </td>
                                <td class="py-3 pr-4 text-gray-dark">
                                    {{ calculateTotalStock(product) }} item
                                </td>
                                <td class="py-3 pr-4">
                                    <span :class="[
                                        product.is_active ? 'text-ink-dark font-bold' : 'text-gray-dark italic opacity-70',
                                        'text-[10px] uppercase tracking-wider'
                                    ]">
                                        {{ product.is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </td>
                                <td class="py-3 text-right space-x-3 whitespace-nowrap">
                                    <Link :href="`/admin/products/${product.id}/edit`" class="text-ink-dark hover:underline font-bold">
                                        Varian & SKU Edit
                                    </Link>
                                    <button @click="deleteProduct(product)" class="text-gray-dark hover:text-ink-dark hover:underline cursor-pointer">
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12 text-xs text-gray-dark">
                    Belum ada produk terdaftar. Klik "+ Tambah Produk Baru" di atas untuk menambahkan.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const calculateTotalStock = (product) => {
    if (!product.variants) return 0;
    return product.variants.reduce((total, variant) => {
        if (!variant.skus) return total;
        return total + variant.skus.reduce((skuTotal, sku) => skuTotal + (sku.stock || 0), 0);
    }, 0);
};

const deleteProduct = (product) => {
    if (confirm(`Apakah Anda yakin ingin menghapus produk "${product.name}" beserta seluruh varian dan ukuran SKU di dalamnya?`)) {
        router.delete(`/admin/products/${product.id}`);
    }
};

const formatPriceSpread = (range) => {
    if (!range || range.min === 0) return 'Rp 0';
    
    const formatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });

    if (range.min === range.max) {
        return formatter.format(range.min);
    }

    return `${formatter.format(range.min)} - ${formatter.format(range.max)}`;
};
</script>
