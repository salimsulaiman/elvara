<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 flex flex-col md:flex-row gap-10 font-sans">
            <!-- Sidebar: Filter Options -->
            <aside class="w-full md:w-64 space-y-8 flex-shrink-0">
                <div class="border-b border-border-soft pb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-ink-dark">Filter Pencarian</h2>
                </div>

                <!-- Category Filter -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-ink-dark uppercase tracking-wide">Kategori</h3>
                    <div class="flex flex-col gap-2">
                        <button 
                            @click="filterCategory('')"
                            :class="[activeCategory === '' ? 'text-ink-dark font-bold underline' : 'text-gray-dark hover:text-ink-dark', 'text-left text-xs cursor-pointer']"
                        >
                            Semua Produk
                        </button>
                        <button 
                            v-for="cat in categories" 
                            :key="cat.id"
                            @click="filterCategory(cat.slug)"
                            :class="[activeCategory === cat.slug ? 'text-ink-dark font-bold underline' : 'text-gray-dark hover:text-ink-dark', 'text-left text-xs cursor-pointer']"
                        >
                            {{ cat.name }}
                        </button>
                    </div>
                </div>

                <!-- Price Filter -->
                <div class="space-y-3">
                    <h3 class="text-xs font-semibold text-ink-dark uppercase tracking-wide">Rentang Harga</h3>
                    <div class="space-y-2">
                        <input 
                            type="number" 
                            v-model="minPrice" 
                            placeholder="Harga Min (Rp)" 
                            class="bg-gray-light text-ink-dark text-xs rounded-md px-3 py-2 w-full focus:bg-white focus:border focus:border-border-dark outline-none"
                        />
                        <input 
                            type="number" 
                            v-model="maxPrice" 
                            placeholder="Harga Max (Rp)" 
                            class="bg-gray-light text-ink-dark text-xs rounded-md px-3 py-2 w-full focus:bg-white focus:border focus:border-border-dark outline-none"
                        />
                        <div class="flex gap-2">
                            <button @click="applyFilters" class="btn-primary py-2 text-xs w-full">Terapkan</button>
                            <button @click="resetPriceFilter" class="btn-secondary py-2 text-xs w-full">Reset</button>
                        </div>
                    </div>
                </div>

                <!-- Active Filters & Reset button -->
                <div v-if="hasActiveFilters" class="pt-4 border-t border-border-soft">
                    <button @click="clearAllFilters" class="btn-outline w-full py-2 text-xs border-dashed text-gray-dark hover:text-ink-dark">
                        Hapus Semua Filter
                    </button>
                </div>
            </aside>

            <!-- Main Display: Product Grid -->
            <section class="flex-grow space-y-6">
                <!-- Header Stats -->
                <div class="flex items-center justify-between border-b border-border-soft pb-4">
                    <h1 class="text-sm font-bold text-ink-dark uppercase tracking-wider">
                        Hasil Katalog ({{ products.length }})
                    </h1>
                    
                    <!-- Search query tag -->
                    <span v-if="searchQuery" class="text-xs text-gray-dark bg-gray-light px-3 py-1 rounded-full">
                        Mencari: "{{ searchQuery }}"
                    </span>
                </div>

                <!-- Catalog Grid -->
                <div v-if="products.length > 0" class="grid grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-10">
                    <div 
                        v-for="product in products" 
                        :key="product.id"
                        class="group relative flex flex-col space-y-3 cursor-pointer"
                        @click="viewProduct(product)"
                    >
                        <!-- Image Container -->
                        <div class="aspect-square bg-gray-light rounded-lg overflow-hidden relative">
                            <img 
                                :src="product.cover_image" 
                                :alt="product.name"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.02] select-none"
                            />
                            <!-- Category Badge -->
                            <span class="absolute top-3 left-3 bg-white text-ink-dark text-[9px] font-bold uppercase tracking-widest px-2.5 py-1 rounded-sm border border-border-soft">
                                {{ product.category?.name }}
                            </span>
                        </div>

                        <!-- Product details -->
                        <div class="space-y-1">
                            <h4 class="font-semibold text-sm text-ink-dark group-hover:underline truncate">{{ product.name }}</h4>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span v-if="product.has_discount" class="text-xs text-gray-dark line-through">
                                    {{ formatPrice(product.price_range.min_original) }}
                                </span>
                                <span class="text-xs text-ink-dark font-bold">
                                    {{ formatPrice(product.price_range.min) }}
                                </span>
                                <span v-if="product.has_discount" class="text-[8px] bg-ink-dark text-white font-bold uppercase tracking-widest px-1.5 py-0.5 rounded-sm">
                                    Promo
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-else class="text-center py-24 bg-gray-light rounded-xl space-y-2">
                    <p class="text-sm font-bold text-ink-dark">Tidak ada produk ditemukan</p>
                    <p class="text-xs text-gray-dark max-w-xs mx-auto leading-relaxed">
                        Coba sesuaikan pencarian kata kunci Anda atau ubah rentang harga dan kategori filter di sidebar.
                    </p>
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const page = usePage();

const categories = computed(() => page.props.categories || []);

const activeCategory = ref(props.filters?.category || '');
const minPrice = ref(props.filters?.min_price || '');
const maxPrice = ref(props.filters?.max_price || '');
const searchQuery = computed(() => props.filters?.search || '');

const hasActiveFilters = computed(() => {
    return activeCategory.value !== '' || minPrice.value !== '' || maxPrice.value !== '' || searchQuery.value !== '';
});

const applyFilters = () => {
    const params = {};
    if (searchQuery.value) params.search = searchQuery.value;
    if (activeCategory.value) params.category = activeCategory.value;
    if (minPrice.value) params.min_price = minPrice.value;
    if (maxPrice.value) params.max_price = maxPrice.value;

    router.visit('/catalog', {
        data: params,
        preserveState: true
    });
};

const filterCategory = (slug) => {
    activeCategory.value = slug;
    applyFilters();
};

const resetPriceFilter = () => {
    minPrice.value = '';
    maxPrice.value = '';
    applyFilters();
};

const clearAllFilters = () => {
    activeCategory.value = '';
    minPrice.value = '';
    maxPrice.value = '';
    router.visit('/catalog');
};

const viewProduct = (product) => {
    if (product.has_discount && product.discounted_sku_id) {
        router.visit(`/product/${product.slug}?sku_id=${product.discounted_sku_id}`);
    } else {
        router.visit(`/product/${product.slug}`);
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
