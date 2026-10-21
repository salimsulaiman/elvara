<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 font-sans">
            <!-- Breadcrumbs -->
            <div class="text-xs text-gray-dark mb-8 flex items-center gap-2">
                <Link href="/" class="hover:text-ink-dark">Beranda</Link>
                <span>/</span>
                <Link href="/catalog" class="hover:text-ink-dark">Katalog</Link>
                <span>/</span>
                <Link :href="`/catalog?category=${product.category?.slug}`" class="hover:text-ink-dark">{{ product.category?.name }}</Link>
                <span>/</span>
                <span class="text-ink-dark font-medium">{{ product.name }}</span>
            </div>

            <!-- Product Details Split Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
                
                <!-- Left Column: Gallery Media Slider -->
                <div class="space-y-4">
                    <div class="aspect-square bg-gray-light rounded-xl overflow-hidden border border-border-soft">
                        <img 
                            :src="activeImage" 
                            :alt="product.name"
                            class="w-full h-full object-cover select-none transition-all duration-300"
                        />
                    </div>

                    <!-- Thumbnails list -->
                    <div v-if="allImages.length > 1" class="flex gap-3 overflow-x-auto pb-2">
                        <button 
                            v-for="(img, idx) in allImages" 
                            :key="idx"
                            @click="activeImage = img"
                            :class="[activeImage === img ? 'border-border-dark' : 'border-border-soft', 'w-20 h-20 rounded-md overflow-hidden border-2 bg-gray-light shrink-0 cursor-pointer']"
                        >
                            <img :src="img" class="w-full h-full object-cover" />
                        </button>
                    </div>
                </div>

                <!-- Right Column: Product Configurations -->
                <div class="space-y-6">
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-gray-dark bg-gray-light px-2.5 py-1 rounded-sm border border-border-soft inline-block">
                            {{ product.category?.name }}
                        </span>
                        <h1 class="text-2xl md:text-3xl font-extrabold text-ink-dark tracking-tight">{{ product.name }}</h1>
                        
                        <!-- Price section -->
                        <div class="mt-2 flex items-baseline gap-3 flex-wrap">
                            <template v-if="selectedSku">
                                <template v-if="selectedSku.active_discount">
                                    <span class="text-xl font-extrabold text-ink-dark">{{ formatPrice(selectedSku.discounted_price) }}</span>
                                    <span class="text-sm text-gray-dark line-through">{{ formatPrice(selectedSku.price) }}</span>
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
                                        {{ selectedSku.active_discount.type === 'percentage' ? `${selectedSku.active_discount.value}% OFF` : `Hemat ${formatPrice(selectedSku.active_discount.value)}` }}
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-xl font-extrabold text-ink-dark">{{ formatPrice(selectedSku.price) }}</span>
                                </template>
                            </template>
                            <template v-else>
                                <template v-if="product.price_range?.has_discount">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl font-extrabold text-ink-dark">
                                            {{ formatPriceRange(product.price_range.min, product.price_range.max) }}
                                        </span>
                                        <span class="text-sm text-gray-dark line-through">
                                            {{ formatPriceRange(product.price_range.min_original, product.price_range.max_original) }}
                                        </span>
                                    </div>
                                </template>
                                <template v-else>
                                    <span class="text-xl font-extrabold text-ink-dark">
                                        {{ formatPriceRange(product.price_range?.min, product.price_range?.max) }}
                                    </span>
                                </template>
                            </template>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="border-t border-b border-border-soft py-4 text-xs text-gray-dark leading-relaxed">
                        <p>{{ product.description }}</p>
                        <p class="mt-3 font-semibold text-ink-dark">Berat Pengiriman: {{ product.weight }} gram (0.25 kg)</p>
                    </div>

                    <!-- Variant Configurator: Colors (Warna) -->
                    <div v-if="product.variants && product.variants.length > 0" class="space-y-3">
                        <span class="text-xs font-semibold text-ink-dark uppercase tracking-wide inline-block">Pilih Varian Warna:</span>
                        <div class="flex flex-wrap gap-3">
                            <button 
                                v-for="variant in product.variants" 
                                :key="variant.id"
                                @click="selectVariant(variant)"
                                :class="[selectedVariant?.id === variant.id ? 'border-border-dark ring-2 ring-border-dark' : 'border-border-soft', 'px-4 py-2 border rounded-md text-xs font-medium bg-white hover:border-border-dark cursor-pointer transition-all flex items-center gap-1.5']"
                            >
                                <span>{{ variant.variant_name }}</span>
                                <Tag v-if="variantHasDiscount(variant)" class="w-3 h-3 text-rose-600 stroke-[2px]" />
                            </button>
                        </div>
                    </div>

                    <!-- Size Configurator: Sizes (Ukuran) -->
                    <div v-if="availableSizes.length > 0" class="space-y-3">
                        <span class="text-xs font-semibold text-ink-dark uppercase tracking-wide inline-block">Pilih Ukuran:</span>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                v-for="sku in availableSizes" 
                                :key="sku.id"
                                @click="selectSku(sku)"
                                :disabled="sku.stock === 0"
                                :class="[
                                    selectedSku?.id === sku.id ? 'bg-ink-dark text-white border-border-dark' : 'bg-white text-ink-dark border-border-soft hover:border-border-dark',
                                    sku.stock === 0 ? 'opacity-35 cursor-not-allowed bg-gray-light text-gray-dark border-dashed' : 'cursor-pointer',
                                    'px-3.5 py-2 border rounded-md text-xs font-medium transition-colors flex items-center justify-center text-center'
                                ]"
                            >
                                <span class="flex flex-col items-center justify-center">
                                    <span class="flex items-center gap-1">
                                        <span>{{ sku.size }}</span>
                                        <Tag v-if="sku.active_discount" :class="[selectedSku?.id === sku.id ? 'text-rose-400' : 'text-rose-600', 'w-3 h-3 stroke-[2px]']" />
                                    </span>
                                    <span v-if="sku.stock <= 3 && sku.stock > 0" class="text-[9px] block mt-0.5 opacity-80">Sisa {{ sku.stock }}</span>
                                    <span v-if="sku.stock === 0" class="text-[9px] block mt-0.5 opacity-80">Habis</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Actions Panel: Qty & Add to Cart -->
                    <div class="border-t border-border-soft pt-6 space-y-4">
                        <div class="flex items-center gap-4">
                            <!-- Qty Selector -->
                            <div class="flex items-center border border-border-soft rounded-md bg-gray-light h-11">
                                <button @click="decQty" class="px-3 h-full hover:bg-border-soft text-sm font-bold cursor-pointer select-none">&#8722;</button>
                                <span class="px-4 text-xs font-bold text-ink-dark select-none">{{ qty }}</span>
                                <button @click="incQty" class="px-3 h-full hover:bg-border-soft text-sm font-bold cursor-pointer select-none">&#43;</button>
                            </div>

                            <!-- Add to Cart Button -->
                            <button 
                                @click="addToCart"
                                :disabled="!selectedSku || selectedSku.stock === 0 || cartProcessing"
                                class="grow btn-primary h-11"
                            >
                                {{ cartProcessing ? 'Memproses...' : (selectedSku ? 'Tambah Ke Keranjang' : 'Pilih Warna & Ukuran') }}
                            </button>

                            <!-- Wishlist Toggle -->
                            <button 
                                @click="toggleWishlist"
                                class="w-11 h-11 border border-border-soft hover:border-border-dark rounded-md flex items-center justify-center cursor-pointer transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" :fill="isWishlisted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" :class="[isWishlisted ? 'text-ink-dark' : 'text-gray-dark', 'w-5 h-5']">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="selectedSku" class="text-[11px] text-gray-dark font-medium">
                            Stok Tersedia: {{ selectedSku.stock }} item | SKU: {{ selectedSku.sku }}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Tag } from 'lucide-vue-next';

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    is_wishlisted: {
        type: Boolean,
        default: false
    },
    preselected_sku_id: {
        type: [Number, String],
        default: null
    }
});

const page = usePage();

const selectedVariant = ref(null);
const selectedSku = ref(null);
const qty = ref(1);
const activeImage = ref('');
const cartProcessing = ref(false);

const isWishlisted = computed(() => props.is_wishlisted);

const variantHasDiscount = (variant) => {
    if (!variant.skus) return false;
    return variant.skus.some(sku => !!sku.active_discount);
};

// Build list of all product images (galleries + variants)
const allImages = computed(() => {
    const imgs = [];
    if (props.product.cover_image) {
        imgs.push(props.product.cover_image);
    }
    props.product.galleries?.forEach(gal => {
        if (!imgs.includes(gal.image_path)) {
            imgs.push(gal.image_path);
        }
    });
    props.product.variants?.forEach(v => {
        if (v.variant_image && !imgs.includes(v.variant_image)) {
            imgs.push(v.variant_image);
        }
    });
    return imgs;
});

// Set initial color variant and SKU, taking into account preselected_sku_id
let initialVariant = null;
let initialSku = null;

if (props.preselected_sku_id && props.product.variants) {
    for (const variant of props.product.variants) {
        const foundSku = (variant.skus || []).find(s => s.id === Number(props.preselected_sku_id));
        if (foundSku) {
            initialVariant = variant;
            initialSku = foundSku;
            break;
        }
    }
}

if (!initialVariant && props.product.variants && props.product.variants.length > 0) {
    initialVariant = props.product.variants[0];
}

selectedVariant.value = initialVariant;
selectedSku.value = initialSku;

// Set initial active image
activeImage.value = selectedVariant.value?.variant_image || props.product.cover_image || '';

// Computed sizes available for the selected variant
const availableSizes = computed(() => {
    if (!selectedVariant.value) return [];
    return selectedVariant.value.skus || [];
});

// Reset SKU when color variant changes, only if it is a different variant
watch(selectedVariant, (newVal, oldVal) => {
    if (oldVal && newVal && newVal.id !== oldVal.id) {
        selectedSku.value = null;
        qty.value = 1;
        if (newVal.variant_image) {
            activeImage.value = newVal.variant_image;
        }
    }
});

// Calculate active price to show
const activePrice = computed(() => {
    if (selectedSku.value) {
        return selectedSku.value.price;
    }
    return props.product.price_range?.min || 0;
});

const selectVariant = (variant) => {
    selectedVariant.value = variant;
};

const selectSku = (sku) => {
    selectedSku.value = sku;
};

const incQty = () => {
    const maxStock = selectedSku.value ? selectedSku.value.stock : 99;
    if (qty.value < maxStock) {
        qty.value++;
    }
};

const decQty = () => {
    if (qty.value > 1) {
        qty.value--;
    }
};

const addToCart = () => {
    if (!page.props.auth?.user) {
        router.visit('/login');
        return;
    }

    if (!selectedSku.value) return;

    cartProcessing.value = true;
    router.post('/cart', {
        product_sku_id: selectedSku.value.id,
        quantity: qty.value
    }, {
        preserveScroll: true,
        onFinish: () => {
            cartProcessing.value = false;
        }
    });
};

const toggleWishlist = () => {
    if (!page.props.auth?.user) {
        router.visit('/login');
        return;
    }
    router.post(`/wishlist/${props.product.id}`, {}, {
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

const formatPriceRange = (min, max) => {
    if (!min) return 'Rp 0';
    if (min === max) return formatPrice(min);
    return `${formatPrice(min)} - ${formatPrice(max)}`;
};
</script>
