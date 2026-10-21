<template>
    <CustomerLayout>
        <div class="space-y-16 pb-20 select-none">
            <!-- Section 1: Hero Banner Carousel -->
            <section class="relative bg-gray-light overflow-hidden h-screen">
                <div v-if="banners.length > 0" class="h-full relative">
                    <div 
                        v-for="(banner, idx) in banners" 
                        :key="banner.id" 
                        v-show="currentSlide === idx"
                        class="absolute inset-0 transition-opacity duration-700 flex items-center"
                    >
                        <!-- Image Cover -->
                        <img 
                            :src="banner.image_path" 
                            :alt="banner.title"
                            class="absolute inset-0 w-full h-full object-cover select-none"
                        />
                        
                        <!-- Overlay text content -->
                        <div class="absolute inset-0 bg-black/40 flex items-center px-12 md:px-24">
                            <div class="max-w-xl text-white space-y-4">
                                <h2 class="text-3xl md:text-5xl font-semibold leading-tight">
                                    {{ banner.title }}
                                </h2>
                                <p class="text-sm md:text-base text-white/80 font-medium">
                                    Koleksi premium edisi monobrand terbaru. Presisi potongan & kenyamanan maksimal.
                                </p>
                                <div class="pt-2">
                                    <Link 
                                        :href="banner.link_url || '/catalog'" 
                                        class="bg-white text-ink-dark hover:bg-gray-light cursor-pointer py-3 px-6 text-xs uppercase tracking-wider font-extrabold"
                                    >
                                        Explore Collection
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slide Controls -->
                    <div v-if="banners.length > 1" class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-3 z-10">
                        <button 
                            v-for="(_, idx) in banners" 
                            :key="idx"
                            @click="currentSlide = idx"
                            :class="[currentSlide === idx ? 'bg-white w-8' : 'bg-white/40 w-2', 'h-2 rounded-full transition-all cursor-pointer']"
                        ></button>
                    </div>
                </div>
                <!-- Fallback banner if empty -->
                <div v-else class="h-full flex items-center justify-center bg-gray-light">
                    <p class="text-gray-dark font-medium tracking-widest uppercase text-xs">ELVARA Minimalist Studio</p>
                </div>
            </section>

            <!-- Section 2: Trust Badges / Value Proposition Bar -->
            <section class="bg-gray-light border-y border-border-soft py-8 select-none">
                <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <ShieldCheck class="w-5 h-5 text-ink-dark flex-shrink-0" />
                        <div>
                            <p class="text-xs font-bold text-ink-dark uppercase tracking-wider">
                                Trusted Quality
                            </p>
                            <p class="text-[11px] text-ink-muted">
                                Produk berkualitas tinggi dengan detail yang terjaga
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3 border-y border-border-soft/60 md:border-y-0 md:border-x md:px-6 py-3 md:py-0">
                        <CreditCard class="w-5 h-5 text-ink-dark flex-shrink-0" />
                        <div>
                            <p class="text-xs font-bold text-ink-dark uppercase tracking-wider">
                                Secure Payment
                            </p>
                            <p class="text-[11px] text-ink-muted">
                                Transaksi aman melalui payment gateway terpercaya
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-3">
                        <PackageCheck class="w-5 h-5 text-ink-dark flex-shrink-0" />
                        <div>
                            <p class="text-xs font-bold text-ink-dark uppercase tracking-wider">
                                Easy Exchange
                            </p>
                            <p class="text-[11px] text-ink-muted">
                                Penukaran ukuran mudah dan tanpa ribet
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 3: Shop by Category (Navigasi Visual) -->
            <section class="max-w-7xl mx-auto px-6 space-y-6">
                <div class="text-center space-y-1">
                    <h3 class="text-xl font-semibold uppercase tracking-widest text-ink-dark">Shop by Category</h3>
                    <p class="text-xs text-gray-dark font-medium">Jelajahi koleksi busana monobrand kami sesuai kebutuhan spesifik Anda.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Category Card: Pakaian -->
                    <Link href="/catalog?category=pakaian" class="group relative block h-[380px] rounded-lg overflow-hidden bg-gray-light border border-border-soft">
                        <img 
                            :src="'/images/pakaian_tshirt_white.png'" 
                            alt="Pakaian"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 select-none"
                        />
                        <div class="absolute inset-0 bg-black/25 group-hover:bg-black/35 transition-colors flex flex-col justify-end p-6">
                            <p class="text-[10px] uppercase tracking-widest text-white/80 font-bold">Koleksi Kaos & Kemeja</p>
                            <h4 class="text-base font-extrabold text-white tracking-wider mt-0.5">PAKAIAN</h4>
                            <div class="mt-3 flex items-center gap-1 text-[10px] text-white font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                <span>Lihat Produk</span>
                                <ChevronRight class="w-3.5 h-3.5" />
                            </div>
                        </div>
                    </Link>
 
                     <!-- Category Card: Sepatu -->
                    <Link href="/catalog?category=sepatu" class="group relative block h-[380px] rounded-lg overflow-hidden bg-gray-light border border-border-soft">
                        <img 
                            :src="'/images/sepatu_sneakers_white.png'" 
                            alt="Sepatu"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 select-none"
                        />
                        <div class="absolute inset-0 bg-black/25 group-hover:bg-black/35 transition-colors flex flex-col justify-end p-6">
                            <p class="text-[10px] uppercase tracking-widest text-white/80 font-bold">Stealth & Urban Sneakers</p>
                            <h4 class="text-base font-extrabold text-white tracking-wider mt-0.5">SEPATU</h4>
                            <div class="mt-3 flex items-center gap-1 text-[10px] text-white font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                <span>Lihat Produk</span>
                                <ChevronRight class="w-3.5 h-3.5" />
                            </div>
                        </div>
                    </Link>
 
                     <!-- Category Card: Jaket -->
                    <Link href="/catalog?category=jaket" class="group relative block h-[380px] rounded-lg overflow-hidden bg-gray-light border border-border-soft">
                        <img 
                            :src="'/images/jaket_varsity_black.png'" 
                            alt="Jaket"
                            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105 select-none"
                        />
                        <div class="absolute inset-0 bg-black/25 group-hover:bg-black/35 transition-colors flex flex-col justify-end p-6">
                            <p class="text-[10px] uppercase tracking-widest text-white/80 font-bold">Premium Outerwear & Bomber</p>
                            <h4 class="text-base font-extrabold text-white tracking-wider mt-0.5">JAKET & OUTERWEAR</h4>
                            <div class="mt-3 flex items-center gap-1 text-[10px] text-white font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                <span>Lihat Produk</span>
                                <ChevronRight class="w-3.5 h-3.5" />
                            </div>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- Section 4: Flash Sale / Limited Vouchers (Urgency & Scarcity) -->
            <section v-if="vouchers && vouchers.length > 0" class="max-w-full sm:max-w-7xl mx-auto px-0 sm:px-6">
                <div class="bg-ink-dark text-white rounded-none sm:rounded-xl p-8 md:p-12 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center border border-white/10 select-none">
                    <!-- Title & Countdown timer -->
                    <div class="space-y-4">
                        <span class="text-[9px] font-bold uppercase tracking-widest text-white/60 bg-white/10 px-2.5 py-1 rounded border border-white/10 inline-block">
                            Penawaran Terbatas
                        </span>
                        <h3 class="text-2xl font-semibold uppercase tracking-wider leading-tight">Flash Sale & Voucher Kilat</h3>
                        <p class="text-xs text-white/70 leading-relaxed font-medium">
                            Ambil voucher belanja eksklusif sebelum kehabisan. Sisa waktu tersisa hari ini:
                        </p>
                        
                        <!-- Countdown display -->
                        <div class="flex items-center gap-2 pt-2">
                            <div class="bg-white/10 border border-white/10 rounded px-3 py-2 text-center w-14">
                                <span class="text-base font-black tracking-tight block font-mono">{{ timeRemaining.hours }}</span>
                                <span class="text-[8px] text-white/50 uppercase tracking-widest font-bold">Jam</span>
                            </div>
                            <span class="text-sm font-bold text-white/50 animate-pulse">:</span>
                            <div class="bg-white/10 border border-white/10 rounded px-3 py-2 text-center w-14">
                                <span class="text-base font-black tracking-tight block font-mono">{{ timeRemaining.minutes }}</span>
                                <span class="text-[8px] text-white/50 uppercase tracking-widest font-bold">Menit</span>
                            </div>
                            <span class="text-sm font-bold text-white/50 animate-pulse">:</span>
                            <div class="bg-white/10 border border-white/10 rounded px-3 py-2 text-center w-14">
                                <span class="text-base font-black tracking-tight block font-mono">{{ timeRemaining.seconds }}</span>
                                <span class="text-[8px] text-white/50 uppercase tracking-widest font-bold">Detik</span>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Items Grid (Taken from existing database) -->
                    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div 
                            v-for="v in vouchers" 
                            :key="v.id"
                            class="bg-white text-ink-dark rounded-lg p-5 border border-border-soft flex flex-col justify-between h-44 relative group overflow-hidden"
                        >
                            <!-- Voucher Info -->
                            <div>
                                <div class="flex justify-between items-start">
                                    <span class="text-[9px] font-bold uppercase tracking-widest text-ink-dark bg-gray-light px-2 py-0.5 rounded border border-border-soft font-mono">
                                        VOUCHER KILAT
                                    </span>
                                    <span class="text-[9px] text-gray-dark font-semibold">Min. Belanja {{ formatPrice(v.min_order_amount) }}</span>
                                </div>
                                <h4 class="text-lg font-black tracking-tight text-ink-dark mt-2 font-mono uppercase">
                                    {{ v.code }}
                                </h4>
                                <p class="text-xs text-gray-dark font-medium mt-1">
                                    Potongan {{ v.type === 'fixed' ? formatPrice(v.reward_value) : `${v.reward_value}%` }}
                                </p>
                            </div>

                            <!-- Quota Indicator (FOMO) -->
                            <div class="space-y-1.5 mt-auto">
                                <div class="flex justify-between text-[9px] font-bold uppercase text-gray-dark tracking-wide">
                                    <span>Sisa Kuota Klaim</span>
                                    <span>{{ v.global_limit - v.global_used }} / {{ v.global_limit }} Klaim</span>
                                </div>
                                <div class="w-full bg-gray-light h-1.5 rounded-full overflow-hidden border border-border-soft/60">
                                    <div 
                                        class="bg-ink-dark h-full transition-all duration-500" 
                                        :style="{ width: `${((v.global_limit - v.global_used) / v.global_limit) * 100}%` }"
                                    ></div>
                                </div>
                                <p class="text-[8px] text-rose-600 font-extrabold uppercase tracking-widest animate-pulse mt-1">
                                    🔥 Terbatas untuk {{ v.global_limit }} user tercepat!
                                </p>
                            </div>

                            <!-- Click to copy overlay or direct to cart link -->
                            <Link 
                                href="/cart"
                                class="absolute inset-0 bg-black/90 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity font-bold text-xs uppercase tracking-wider cursor-pointer"
                            >
                                Gunakan di Keranjang &rarr;
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section 5: New Arrivals / Trending Products (Product Grid) -->
            <section class="max-w-7xl mx-auto px-6 space-y-6">
                <div class="flex items-end justify-between border-b border-border-soft pb-4">
                    <div class="space-y-1">
                        <h3 class="text-xl font-semibold uppercase tracking-widest text-ink-dark">New Arrivals</h3>
                        <p class="text-xs text-gray-dark font-medium">Koleksi teranyar dengan potongan minimalis kontemporer.</p>
                    </div>
                    <Link href="/catalog" class="text-xs font-bold text-ink-dark hover:underline flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <span>&rarr;</span>
                    </Link>
                </div>

                <div v-if="new_arrivals && new_arrivals.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div 
                        v-for="(product, idx) in new_arrivals" 
                        :key="product.id"
                        class="group relative flex flex-col space-y-3 cursor-pointer"
                        @click="viewProduct(product)"
                        @mouseenter="hoveredProductId = product.id"
                        @mouseleave="hoveredProductId = null"
                    >
                        <!-- Product image container -->
                        <div class="aspect-square bg-gray-light rounded-lg overflow-hidden relative border border-border-soft select-none">
                            <img 
                                :src="hoveredProductId === product.id && getHoverImage(product) ? getHoverImage(product) : product.cover_image" 
                                :alt="product.name"
                                class="w-full h-full object-cover transition-all duration-500 group-hover:scale-[1.02]"
                            />
                            
                            <!-- Badges (Top Left) -->
                            <div class="absolute top-3 left-3 flex flex-col gap-1.5 items-start">
                                <span v-if="idx < 4" class="bg-ink-dark text-white text-[8px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-sm border border-transparent">
                                    NEW
                                </span>
                                <span v-if="product.has_discount" class="bg-rose-600 text-white text-[8px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-sm">
                                    PROMO
                                </span>
                            </div>

                            <!-- Quick Wishlist Button (Top Right) -->
                            <button 
                                @click="toggleWishlist($event, product)"
                                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 border border-border-soft flex items-center justify-center text-gray-dark hover:text-rose-600 hover:bg-white cursor-pointer shadow-sm hover:scale-105 transition-all z-10"
                                title="Tambahkan ke Wishlist"
                            >
                                <Heart 
                                    :class="[isWishlisted(product) ? 'text-rose-600 fill-rose-600' : 'text-gray-dark', 'w-4 h-4 transition-colors']" 
                                />
                            </button>
                        </div>

                        <!-- Product info -->
                        <div class="space-y-1">
                            <h4 class="font-bold text-xs md:text-sm text-ink-dark group-hover:underline truncate">{{ product.name }}</h4>
                            <p class="text-[9px] uppercase font-bold text-gray-dark tracking-wide">{{ product.category?.name }}</p>
                            
                            <div class="flex flex-wrap items-baseline gap-2 pt-0.5">
                                <template v-if="product.has_discount">
                                    <span class="text-xs font-bold text-ink-dark">
                                        {{ formatPrice(product.price_range.min) }}
                                    </span>
                                    <span class="text-[10px] text-gray-dark line-through">
                                        {{ formatPrice(product.price_range.min_original) }}
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-xs font-bold text-ink-dark">
                                        {{ formatPrice(product.price_range.min) }}
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-12 bg-gray-light rounded-lg">
                    <p class="text-xs text-gray-dark font-medium">Belum ada produk terbaru yang ditambahkan.</p>
                </div>
            </section>

            <!-- Section 6: Editorial / Brand Story Lookbook (Emotional Connection) -->
            <section class="max-w-full sm:max-w-7xl mx-auto px-0 sm:px-6 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center bg-gray-light/30 rounded-none sm:rounded-xl border border-border-soft p-8 md:p-12">
                    <!-- Photo lookbook -->
                    <div class="aspect-4/3 rounded-lg overflow-hidden border border-border-soft relative group bg-gray-light">
                        <img 
                            :src="'/images/banner_promo_1.png'" 
                            alt="Elvara Studio Kampanye" 
                            class="w-full h-full object-cover select-none transition-transform duration-700 group-hover:scale-102"
                        />
                        <div class="absolute inset-0 bg-ink-dark/10 group-hover:bg-ink-dark/20 transition-colors"></div>
                    </div>

                    <!-- Lookbook Text Narrative -->
                    <div class="space-y-6 flex flex-col items-start text-left">
                        <span class="text-[9px] font-bold uppercase tracking-widest text-gray-dark bg-gray-light px-2.5 py-1 rounded-sm border border-border-soft inline-block">
                            Our Studio Story
                        </span>
                        
                        <h3 class="text-2xl md:text-3xl font-semibold uppercase tracking-wider text-ink-dark leading-tight">
                            Esensi Minimalis,<br>Presisi Estetika Monobrand.
                        </h3>
                        
                        <div class="space-y-4 text-xs text-gray-dark leading-relaxed font-medium">
                            <p>
                                Elvara lahir dari keyakinan bahwa pakaian bukanlah sekadar apa yang menutupi tubuh, melainkan proyeksi dari ketenangan, kedewasaan, dan fungsionalitas murni. Setiap produk dirancang di studio kami dengan ketelitian rancang bangun terbaik.
                            </p>
                            <p>
                                Kami mengeliminasi dekorasi berlebih untuk memberikan kebebasan bagi struktur potongan kain, kerapihan jahitan, dan harmoni palet warna netral. Melalui matriks varian SKU terpadu dan sistem logistik presisi, kami mempertemukan kualitas butik dengan efisiensi digital modern.
                            </p>
                        </div>

                        <div class="pt-2">
                            <Link href="/catalog" class="btn-primary py-3 px-6 text-xs font-bold inline-flex items-center gap-2">
                                <span>Explore Studio Collection</span>
                                <ChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { ShieldCheck, CreditCard, PackageCheck, Heart, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    banners: {
        type: Array,
        default: () => []
    },
    new_arrivals: {
        type: Array,
        default: () => []
    },
    vouchers: {
        type: Array,
        default: () => []
    },
    wishlist_product_ids: {
        type: Array,
        default: () => []
    }
});

const currentSlide = ref(0);
let slideInterval = null;
const hoveredProductId = ref(null);
const countdown = ref({ hours: '00', minutes: '00', seconds: '00' });
const timeRemaining = ref({ hours: '00', minutes: '00', seconds: '00' });
let timerInterval = null;

const startCountdown = () => {
    const updateTimer = () => {
        const now = new Date();
        const tomorrow = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
        const diff = tomorrow - now;

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff / (1000 * 60)) % 60);
        const seconds = Math.floor((diff / 1000) % 60);

        timeRemaining.value = {
            hours: String(hours).padStart(2, '0'),
            minutes: String(minutes).padStart(2, '0'),
            seconds: String(seconds).padStart(2, '0')
        };
    };

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
};

onMounted(() => {
    startCountdown();
    if (props.banners.length > 1) {
        slideInterval = setInterval(() => {
            currentSlide.value = (currentSlide.value + 1) % props.banners.length;
        }, 6000); // Rotate banners every 6 seconds
    }
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
    if (timerInterval) clearInterval(timerInterval);
});

const getHoverImage = (product) => {
    // Return the second variant image if available
    if (product.variants && product.variants.length > 1 && product.variants[1].variant_image) {
        return product.variants[1].variant_image;
    }
    // Or return the second gallery image if available
    if (product.galleries && product.galleries.length > 1 && product.galleries[1].image_path) {
        return product.galleries[1].image_path;
    }
    return null;
};

const isWishlisted = (product) => {
    return props.wishlist_product_ids.includes(product.id);
};

const toggleWishlist = (e, product) => {
    e.stopPropagation(); // prevent clicking card to go to product details
    if (!usePage().props.auth?.user) {
        router.visit('/login');
        return;
    }
    router.post(`/wishlist/${product.id}`, {}, {
        preserveScroll: true
    });
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
</script>
