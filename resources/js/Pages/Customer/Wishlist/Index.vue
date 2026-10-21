<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 font-sans">
            <!-- Header Stats -->
            <div class="flex items-center justify-between border-b border-border-soft pb-4 mb-8">
                <div class="space-y-1">
                    <h1 class="text-sm font-bold text-ink-dark uppercase tracking-wider">
                        Wishlist Saya ({{ products.length }})
                    </h1>
                    <p class="text-xs text-gray-dark font-medium">Daftar produk yang Anda simpan untuk dibeli nanti.</p>
                </div>
                <Link href="/catalog" class="text-xs font-bold text-ink-dark hover:underline flex items-center gap-1">
                    <span>Lanjut Belanja</span>
                    <span>&rarr;</span>
                </Link>
            </div>

            <!-- Wishlist Products Grid -->
            <div v-if="products.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div 
                    v-for="product in products" 
                    :key="product.id"
                    class="group relative flex flex-col space-y-3 cursor-pointer"
                    @click="viewProduct(product)"
                >
                    <!-- Image Container -->
                    <div class="aspect-square bg-gray-light rounded-lg overflow-hidden relative border border-border-soft select-none">
                        <img 
                            :src="product.cover_image" 
                            :alt="product.name"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                        />
                        <!-- Category Badge (Top Left) -->
                        <span class="absolute top-3 left-3 bg-white text-ink-dark text-[9px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-sm border border-border-soft">
                            {{ product.category?.name }}
                        </span>

                        <!-- Quick Remove Button (Top Right) -->
                        <button 
                            @click="removeWishlist($event, product)"
                            class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/95 border border-border-soft flex items-center justify-center text-rose-600 hover:text-rose-700 hover:bg-white cursor-pointer shadow-sm hover:scale-105 transition-all z-10"
                            title="Hapus dari Wishlist"
                        >
                            <Heart class="w-4 h-4 fill-current text-rose-600" />
                        </button>
                    </div>

                    <!-- Product Details -->
                    <div class="space-y-1">
                        <h4 class="font-bold text-xs md:text-sm text-ink-dark group-hover:underline truncate">{{ product.name }}</h4>
                        <p class="text-[9px] uppercase font-bold text-gray-dark tracking-wide">{{ product.category?.name }}</p>
                        
                        <!-- Price Range Details -->
                        <div class="flex flex-wrap items-baseline gap-2 pt-0.5">
                            <template v-if="product.has_discount">
                                <span class="text-xs font-bold text-ink-dark">
                                    {{ formatPrice(product.price_range.min) }}
                                </span>
                                <span class="text-[10px] text-gray-dark line-through">
                                    {{ formatPrice(product.price_range.min_original) }}
                                </span>
                                <span class="text-[8px] bg-rose-600 text-white font-bold uppercase tracking-widest px-1.5 py-0.5 rounded-sm">
                                    Promo
                                </span>
                            </template>
                            <template v-else>
                                <span class="text-xs font-bold text-ink-dark">
                                    {{ formatPrice(product.price_range.min) }}
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Action Details CTA -->
                    <div class="pt-2 mt-auto">
                        <span class="inline-flex w-full items-center justify-center bg-gray-light text-ink-dark group-hover:bg-ink-dark group-hover:text-white border border-border-soft group-hover:border-ink-dark py-2 rounded text-[10px] font-bold uppercase tracking-wider transition-colors duration-150">
                            Lihat Detail &rarr;
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="text-center py-24 bg-gray-light rounded-xl space-y-4 max-w-lg mx-auto border border-border-soft">
                <div class="w-16 h-16 rounded-full bg-white border border-border-soft flex items-center justify-center mx-auto text-gray-dark/40">
                    <Heart class="w-7 h-7 stroke-[1.5]" />
                </div>
                <div class="space-y-1.5">
                    <h2 class="text-sm font-bold text-ink-dark uppercase tracking-wider">Wishlist Anda Kosong</h2>
                    <p class="text-xs text-gray-dark max-w-xs mx-auto leading-relaxed">
                        Anda belum menambahkan produk apa pun ke daftar favorit Anda. Temukan produk impian Anda sekarang!
                    </p>
                </div>
                <div class="pt-2">
                    <Link href="/catalog" class="btn-primary text-xs tracking-wider uppercase font-bold py-2.5 px-6">
                        Telusuri Katalog
                    </Link>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Heart } from 'lucide-vue-next';

defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const viewProduct = (product) => {
    if (product.has_discount && product.discounted_sku_id) {
        router.visit(`/product/${product.slug}?sku_id=${product.discounted_sku_id}`);
    } else {
        router.visit(`/product/${product.slug}`);
    }
};

const removeWishlist = (e, product) => {
    e.stopPropagation();
    router.post(`/wishlist/${product.id}`, {}, {
        preserveScroll: true
    });
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
