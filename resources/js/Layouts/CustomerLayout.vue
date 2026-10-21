<template>
    <div class="min-h-screen flex flex-col bg-white text-ink-dark selection:bg-gray-light">
        <!-- Global Full Page Loading Overlay -->
        <LoadingOverlay v-if="isPageLoading" />

        <!-- Bottom Right Flash Alert (Floating Toast) -->
        <div 
            v-if="flashSuccess || flashError" 
            :key="flashSuccess + flashError + flashKey"
            class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white border border-border-soft rounded-lg p-4 flex items-center justify-between transition-all duration-300 shadow-lg overflow-hidden"
        >
            <div class="flex items-center gap-3">
                <span v-if="flashSuccess" class="w-2 h-2 rounded-full bg-ink-dark"></span>
                <span v-else class="w-2 h-2 rounded-full bg-gray-dark"></span>
                <p class="text-sm font-medium">{{ flashSuccess || flashError }}</p>
            </div>
            <button @click="clearFlash" class="text-gray-dark hover:text-ink-dark text-xs cursor-pointer ml-3 z-10">✕</button>
            
            <!-- Countdown Progress Bar -->
            <div 
                class="absolute bottom-0 left-0 w-full h-1 animate-toast-countdown"
                :class="flashSuccess ? 'bg-ink-dark' : 'bg-gray-dark'"
            ></div>
        </div>

        <!-- A. Top Announcement Bar -->
        <div class="bg-ink-dark text-white text-[10px] py-3 px-6 flex items-center justify-between font-bold uppercase tracking-wider select-none border-b border-white/5">
            <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
                <!-- Urgent Marketing Message (Ticker-like / static message) -->
                <div class="flex items-center gap-2">
                    <span>Gratis Ongkir se-Indonesia dengan Voucher 'ELVARANEW' • Diskon 15% Akhir Tahun</span>
                </div>

                <!-- Quick Links (Right side) -->
                <div class="hidden sm:flex items-center gap-4 text-white/80">
                    <Link href="/orders" class="hover:text-white transition-colors">Lacak Pesanan</Link>
                    <span class="text-white/20">|</span>
                    <a href="https://wa.me/628123456789" target="_blank" class="hover:text-white transition-colors">Customer Service</a>
                </div>
            </div>
        </div>

        <!-- B. Global Header Navbar -->
        <header class="sticky top-0 z-40 bg-white border-b border-border-soft h-20 flex items-center px-6 relative">
            <!-- Mobile Full-Width Search Overlay -->
            <div v-if="mobileSearchActive" class="absolute inset-0 bg-white px-6 flex items-center gap-4 z-50">
                <input 
                    type="text" 
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                    placeholder="Cari produk..." 
                    class="bg-gray-light text-ink-dark placeholder:text-gray-dark text-xs rounded-md px-3.5 py-2.5 flex-grow focus:bg-white focus:border focus:border-border-dark outline-none transition-all duration-200 border border-transparent"
                    ref="mobileSearchInput"
                />
                <button @click="mobileSearchActive = false" class="text-xs font-bold text-gray-dark hover:text-ink-dark cursor-pointer">Batal</button>
            </div>

            <div class="max-w-7xl w-full mx-auto flex items-center justify-between">
                <!-- Brand Logo & Navigation -->
                <div class="flex items-center gap-10">
                    <Link href="/" class="text-xl font-black tracking-widest text-ink-dark hover:opacity-85 transition-opacity font-sans select-none">
                        <img :src="'/images/logo/logo.png'" alt="ELVARA" class="h-10 w-auto">
                    </Link>

                    <!-- Mega-Menu Navigation (Katalog Utama) -->
                    <nav class="hidden lg:flex items-center gap-6 text-[10px] font-bold uppercase tracking-wider">
                        <Link v-for="cat in categories" 
                           :key="cat.id" 
                           :href="`/catalog?category=${cat.slug}`"
                           :class="[page.url === `/catalog?category=${cat.slug}` ? 'text-ink-dark border-b-2 border-border-dark' : 'text-gray-dark hover:text-ink-dark', 'pb-1 transition-colors']">
                            {{ cat.name === 'Jaket' ? 'Jaket & Outerwear' : cat.name }}
                        </Link>
                        <Link href="/catalog?category=aksesoris" :class="[page.url === '/catalog?category=aksesoris' ? 'text-ink-dark border-b-2 border-border-dark' : 'text-gray-dark hover:text-ink-dark', 'pb-1 transition-colors']">
                            Aksesoris
                        </Link>
                        <Link href="/catalog" :class="[page.url === '/catalog' && !page.url.includes('promo') ? 'text-ink-dark border-b-2 border-border-dark' : 'text-gray-dark hover:text-ink-dark', 'pb-1 transition-colors']">
                            Koleksi Terbaru
                        </Link>
                        <!-- Promo / Sale with accent color -->
                        <Link href="/catalog?promo=1" :class="[page.url.includes('promo=1') ? 'text-rose-600 border-b-2 border-rose-600' : 'text-rose-600 hover:text-rose-700', 'pb-1 transition-colors font-extrabold flex items-center gap-1.5']">
                            <span>Promo / Sale</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 inline-block animate-pulse"></span>
                        </Link>
                    </nav>
                </div>

                <!-- Actions Section (Search, Cart, Profile) -->
                <div class="flex items-center gap-4">
                    <!-- Mobile Search Icon Trigger -->
                    <button @click="toggleMobileSearch" class="sm:hidden p-1 text-ink-dark hover:opacity-80 transition-opacity cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.602 10.602Z" />
                        </svg>
                    </button>

                    <!-- Omni-Search Bar (Pencarian Pintar) - Desktop Only -->
                    <div class="relative hidden sm:block" id="predictive-search-container">
                        <input 
                            type="text" 
                            v-model="searchQuery"
                            @keyup.enter="handleSearch"
                            @focus="showPredictiveBox = searchQuery.trim().length >= 3"
                            placeholder="Cari sepatu kets, jaket varsity, kaos..." 
                            class="bg-gray-light text-ink-dark placeholder:text-gray-dark text-xs rounded-md px-3.5 py-2.5 w-64 focus:w-80 focus:bg-white focus:border focus:border-border-dark outline-none transition-all duration-200 border border-transparent"
                        />
                        
                        <!-- AJAX Predictive Search Dropdown -->
                        <div 
                            v-if="showPredictiveBox" 
                            class="absolute top-11 right-0 w-80 bg-white border border-border-soft rounded-md shadow-sm z-50 p-3 space-y-2"
                        >
                            <p class="text-[9px] font-bold text-gray-dark uppercase tracking-wider">Rekomendasi Produk</p>
                            
                            <div v-if="searchLoading" class="text-center py-4 text-xs text-gray-dark">
                                Mencari produk...
                            </div>
                            
                            <div v-else-if="searchResults.length === 0" class="text-center py-4 text-xs text-gray-dark">
                                Produk tidak ditemukan.
                            </div>
                            
                            <div v-else class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                                <Link 
                                    v-for="p in searchResults" 
                                    :key="p.slug" 
                                    :href="`/product/${p.slug}`"
                                    class="flex items-center gap-3 p-1.5 rounded hover:bg-gray-light/50 transition-colors group text-left"
                                >
                                    <img :src="p.cover_image" class="w-10 h-10 rounded object-cover flex-shrink-0" />
                                    <div class="min-w-0 flex-grow">
                                        <p class="text-xs font-bold text-ink-dark group-hover:underline truncate">{{ p.name }}</p>
                                        <p class="text-[9px] text-gray-dark mt-0.5 uppercase tracking-wide">{{ p.category_name }}</p>
                                    </div>
                                    <div class="text-right flex-shrink-0 flex flex-col items-end">
                                        <span class="text-xs font-bold text-ink-dark">{{ formatPrice(p.price_range.min) }}</span>
                                        <span v-if="p.price_range.has_discount" class="text-[9px] text-gray-dark line-through mt-0.5">
                                            {{ formatPrice(p.price_range.min_original) }}
                                        </span>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Wishlist Icon with Badge - Desktop only -->
                    <Link href="/wishlist" class="hidden lg:inline-flex relative p-1 text-ink-dark hover:opacity-80 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        <span v-if="wishlistCount > 0" class="absolute -top-1.5 -right-1.5 bg-ink-dark text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                            {{ wishlistCount }}
                        </span>
                    </Link>

                    <!-- Cart Icon with Badge -->
                    <Link href="/cart" class="relative p-1 text-ink-dark hover:opacity-80 transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        <span v-if="cartCount > 0" class="absolute -top-1.5 -right-1.5 bg-ink-dark text-white text-[9px] font-bold rounded-full w-4 h-4 flex items-center justify-center">
                            {{ cartCount }}
                        </span>
                    </Link>

                    <!-- User Profile Dropdown / Login Avatar -->
                    <div class="relative">
                        <div v-if="user" class="flex items-center gap-2">
                            <button @click="toggleDropdown" class="w-8 h-8 rounded-full bg-gray-light hover:bg-border-soft flex items-center justify-center text-xs font-bold text-ink-dark transition-colors cursor-pointer select-none">
                                {{ user.initials }}
                            </button>
                            <!-- Dropdown Menu -->
                            <div v-if="dropdownOpen" class="absolute right-0 top-10 bg-white border border-border-soft rounded-lg py-2 w-48 shadow-sm z-50">
                                <div class="px-4 py-2 border-b border-border-soft text-left">
                                    <p class="text-xs font-semibold text-ink-dark truncate">{{ user.name }}</p>
                                    <p class="text-[10px] text-gray-dark truncate">{{ user.email }}</p>
                                </div>
                                <Link href="/orders" class="block px-4 py-2 text-xs text-gray-dark hover:bg-gray-light hover:text-ink-dark text-left">Riwayat Pesanan</Link>
                                <Link v-if="user.role === 'admin'" href="/admin" class="block px-4 py-2 text-xs text-gray-dark hover:bg-gray-light hover:text-ink-dark font-medium text-left">Dashboard Admin</Link>
                                <button @click="logout" class="w-full text-left block px-4 py-2 text-xs text-gray-dark hover:bg-gray-light hover:text-ink-dark border-t border-border-soft cursor-pointer">Keluar (Logout)</button>
                            </div>
                        </div>
                        <div v-else class="flex items-center gap-2">
                            <!-- Desktop Links -->
                            <div class="hidden sm:flex items-center gap-2">
                                <Link href="/login" class="text-xs font-semibold text-ink-dark hover:underline">Masuk</Link>
                                <span class="text-xs text-border-soft">/</span>
                                <Link href="/register" class="text-xs font-semibold text-ink-dark hover:underline">Daftar</Link>
                            </div>
                            <!-- Mobile User Icon Link -->
                            <Link href="/login" class="sm:hidden p-1 text-ink-dark hover:opacity-80 transition-opacity">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body Content Slot -->
        <main class="flex-grow pb-16 lg:pb-0">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-light border-t border-border-soft py-10 px-8 text-gray-dark text-xs mt-auto">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2 space-y-4">
                    <img :src="'/images/logo/logo.png'" alt="ELVARA" class="h-10 w-auto">
                    <p class="max-w-sm leading-relaxed text-gray-dark">
                        Platform busana monobrand multi-kategori dengan fokus pada potongan minimalis, presisi struktur varian SKU, dan estetika warna bersih.
                    </p>
                </div>
                <div>
                    <p class="font-bold text-ink-dark mb-3">Toko Online</p>
                    <ul class="space-y-2">
                        <li><Link href="/catalog?category=pakaian" class="hover:text-ink-dark">Pakaian</Link></li>
                        <li><Link href="/catalog?category=sepatu" class="hover:text-ink-dark">Sepatu</Link></li>
                        <li><Link href="/catalog?category=jaket" class="hover:text-ink-dark">Jaket</Link></li>
                    </ul>
                </div>
                <div>
                    <p class="font-bold text-ink-dark mb-3">Bantuan & Informasi</p>
                    <ul class="space-y-2">
                        <li><Link href="/orders" class="hover:text-ink-dark">Lacak Pesanan</Link></li>
                        <li><span class="text-gray-dark opacity-60">Syarat & Ketentuan</span></li>
                        <li><span class="text-gray-dark opacity-60">Kebijakan Privasi</span></li>
                    </ul>
                </div>
            </div>
            <div class="max-w-7xl mx-auto border-t border-border-soft mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p>&copy; 2026 ELVARA. All rights reserved.</p>
                <p>Designed with High Contrast & Zero Shadow.</p>
            </div>
        </footer>

        <!-- Modern Minimalist Mobile Bottom Navbar -->
        <nav class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-border-soft h-16 flex items-center justify-around lg:hidden shadow-sm">
            <!-- Home -->
            <Link href="/" class="flex flex-col items-center justify-center text-center gap-1 py-1 flex-grow select-none" :class="page.url === '/' ? 'text-ink-dark' : 'text-gray-dark'">
                <Home class="w-5 h-5" :class="page.url === '/' ? 'stroke-[2.5px]' : 'stroke-[1.8px]'" />
                <span class="text-[8px] font-extrabold uppercase tracking-wider">Home</span>
            </Link>
            <!-- Catalog -->
            <Link href="/catalog" class="flex flex-col items-center justify-center text-center gap-1 py-1 flex-grow select-none" :class="page.url.startsWith('/catalog') && !page.url.includes('promo=1') ? 'text-ink-dark' : 'text-gray-dark'">
                <Compass class="w-5 h-5" :class="page.url.startsWith('/catalog') && !page.url.includes('promo=1') ? 'stroke-[2.5px]' : 'stroke-[1.8px]'" />
                <span class="text-[8px] font-extrabold uppercase tracking-wider">Katalog</span>
            </Link>
            <!-- Wishlist -->
            <Link href="/wishlist" class="flex flex-col items-center justify-center text-center gap-1 py-1 flex-grow select-none relative" :class="page.url === '/wishlist' ? 'text-ink-dark' : 'text-gray-dark'">
                <div class="relative">
                    <Heart class="w-5 h-5" :class="[page.url === '/wishlist' ? 'text-ink-dark fill-ink-dark stroke-[2.5px]' : 'text-gray-dark stroke-[1.8px]']" />
                    <span v-if="wishlistCount > 0" class="absolute -top-1.5 -right-1.5 bg-ink-dark text-white text-[8px] font-bold rounded-full w-3.5 h-3.5 flex items-center justify-center">
                        {{ wishlistCount }}
                    </span>
                </div>
                <span class="text-[8px] font-extrabold uppercase tracking-wider">Wishlist</span>
            </Link>
            <!-- Promo -->
            <Link href="/catalog?promo=1" class="flex flex-col items-center justify-center text-center gap-1 py-1 flex-grow select-none" :class="page.url.includes('promo=1') ? 'text-rose-600 font-extrabold' : 'text-gray-dark'">
                <Percent class="w-5 h-5 animate-pulse" :class="page.url.includes('promo=1') ? 'text-rose-600 stroke-[2.5px]' : 'text-gray-dark stroke-[1.8px]'" />
                <span class="text-[8px] font-extrabold uppercase tracking-wider">Promo</span>
            </Link>
            <!-- Orders -->
            <Link href="/orders" class="flex flex-col items-center justify-center text-center gap-1 py-1 flex-grow select-none" :class="page.url === '/orders' ? 'text-ink-dark' : 'text-gray-dark'">
                <Truck class="w-5 h-5" :class="page.url === '/orders' ? 'stroke-[2.5px]' : 'stroke-[1.8px]'" />
                <span class="text-[8px] font-extrabold uppercase tracking-wider">Lacak</span>
            </Link>
        </nav>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import { Home, Compass, Heart, Percent, Truck } from 'lucide-vue-next';

const page = usePage();

const searchQuery = ref(page.props.filters?.search || '');
const dropdownOpen = ref(false);
const mobileSearchActive = ref(false);
const mobileSearchInput = ref(null);
const isPageLoading = ref(false);

const toggleMobileSearch = () => {
    mobileSearchActive.value = !mobileSearchActive.value;
};

watch(mobileSearchActive, (newVal) => {
    if (newVal) {
        setTimeout(() => {
            mobileSearchInput.value?.focus();
        }, 100);
    }
});

const user = computed(() => page.props.auth?.user);
const categories = computed(() => page.props.categories || []);
const cartCount = computed(() => page.props.cart_count || 0);
const wishlistCount = computed(() => page.props.wishlist_count || 0);

// Flash Messages
const flashSuccess = ref('');
const flashError = ref('');
const flashKey = ref(0);
let flashTimeout = null;

// AJAX Predictive Search States
const searchResults = ref([]);
const searchLoading = ref(false);
const showPredictiveBox = ref(false);
let searchTimeout = null;

// Watch search query to trigger predictive search
watch(searchQuery, (newVal) => {
    if (searchTimeout) clearTimeout(searchTimeout);

    const query = newVal.trim();
    if (query.length < 3) {
        searchResults.value = [];
        showPredictiveBox.value = false;
        return;
    }

    searchTimeout = setTimeout(async () => {
        searchLoading.value = true;
        showPredictiveBox.value = true;
        try {
            const response = await fetch(`/api/search-predictive?query=${encodeURIComponent(query)}`);
            const result = await response.json();
            if (result.status === 'success') {
                searchResults.value = result.data;
            }
        } catch (e) {
            console.error('Error fetching predictive search:', e);
        } finally {
            searchLoading.value = false;
        }
    }, 300);
});

const updateFlash = () => {
    flashSuccess.value = page.props.flash?.success || '';
    flashError.value = page.props.flash?.error || '';
    
    if (flashSuccess.value || flashError.value) {
        if (flashTimeout) clearTimeout(flashTimeout);
        flashKey.value++; // Increment key to force re-render and restart animation
        flashTimeout = setTimeout(() => {
            clearFlash();
        }, 5000); // Autoclose after 5 seconds
    }
};

const clearFlash = () => {
    if (flashTimeout) {
        clearTimeout(flashTimeout);
        flashTimeout = null;
    }
    flashSuccess.value = '';
    flashError.value = '';
    page.props.flash.success = null;
    page.props.flash.error = null;
};

// Update flash messages when page reloads/mutates
router.on('finish', () => {
    updateFlash();
    showPredictiveBox.value = false; // close predictive search on page navigate
});

onMounted(() => {
    updateFlash();
    document.addEventListener('click', closeDropdownOnOutsideClick);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropdownOnOutsideClick);
});

const toggleDropdown = (e) => {
    e.stopPropagation();
    dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdownOnOutsideClick = (e) => {
    dropdownOpen.value = false;
    
    // Close predictive search if click is outside search container
    const container = document.getElementById('predictive-search-container');
    if (container && !container.contains(e.target)) {
        showPredictiveBox.value = false;
    }
};

const handleSearch = () => {
    showPredictiveBox.value = false;
    if (searchQuery.value.trim()) {
        router.visit(`/catalog?search=${encodeURIComponent(searchQuery.value.trim())}`);
    } else {
        router.visit('/catalog');
    }
};

const logout = () => {
    router.post('/logout');
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
