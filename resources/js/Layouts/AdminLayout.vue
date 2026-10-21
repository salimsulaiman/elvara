<template>
    <div class="min-h-screen flex bg-white text-ink-dark selection:bg-gray-light font-sans">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-light border-r border-border-soft flex flex-col fixed h-full z-30">
            <div class="p-6 border-b border-border-soft">
                <Link href="/admin" class="text-lg font-bold tracking-widest text-ink-dark">
                    ELVARA ADMIN
                </Link>
                <p class="text-[10px] text-gray-dark uppercase tracking-wider mt-1">Control Panel</p>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-grow p-4 space-y-1">
                <Link href="/admin" :class="[isUrl('/admin') ? 'bg-white text-ink-dark font-semibold border border-border-soft' : 'text-gray-dark hover:text-ink-dark hover:bg-white/50', 'flex items-center gap-3 px-4 py-3 text-xs rounded-md transition-all']">
                    <span>Dashboard</span>
                </Link>
                <Link href="/admin/categories" :class="[isUrl('/admin/categories') ? 'bg-white text-ink-dark font-semibold border border-border-soft' : 'text-gray-dark hover:text-ink-dark hover:bg-white/50', 'flex items-center gap-3 px-4 py-3 text-xs rounded-md transition-all']">
                    <span>Kategori Produk</span>
                </Link>
                <Link href="/admin/products" :class="[isUrl('/admin/products') ? 'bg-white text-ink-dark font-semibold border border-border-soft' : 'text-gray-dark hover:text-ink-dark hover:bg-white/50', 'flex items-center gap-3 px-4 py-3 text-xs rounded-md transition-all']">
                    <span>Produk & SKU Matrix</span>
                </Link>
                <Link href="/admin/vouchers" :class="[isUrl('/admin/vouchers') ? 'bg-white text-ink-dark font-semibold border border-border-soft' : 'text-gray-dark hover:text-ink-dark hover:bg-white/50', 'flex items-center gap-3 px-4 py-3 text-xs rounded-md transition-all']">
                    <span>Voucher Diskon</span>
                </Link>
                <Link href="/admin/discounts" :class="[isUrl('/admin/discounts') ? 'bg-white text-ink-dark font-semibold border border-border-soft' : 'text-gray-dark hover:text-ink-dark hover:bg-white/50', 'flex items-center gap-3 px-4 py-3 text-xs rounded-md transition-all']">
                    <span>Diskon Produk</span>
                </Link>
                <Link href="/admin/banners" :class="[isUrl('/admin/banners') ? 'bg-white text-ink-dark font-semibold border border-border-soft' : 'text-gray-dark hover:text-ink-dark hover:bg-white/50', 'flex items-center gap-3 px-4 py-3 text-xs rounded-md transition-all']">
                    <span>Banner Promo</span>
                </Link>
            </nav>

            <!-- Sidebar Footer Options -->
            <div class="p-4 border-t border-border-soft space-y-1">
                <Link href="/" class="flex items-center gap-3 px-4 py-2.5 text-xs text-gray-dark hover:text-ink-dark hover:bg-white/50 rounded-md transition-all">
                    <span>Kembali ke Toko</span>
                </Link>
                <button @click="logout" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-xs text-gray-dark hover:text-ink-dark hover:bg-white/50 rounded-md transition-all cursor-pointer">
                    <span>Keluar (Logout)</span>
                </button>
            </div>
        </aside>

        <!-- Main Workspace Area -->
        <div class="flex-grow pl-64 flex flex-col min-h-screen">
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

            <!-- Page Workspace Content -->
            <main class="flex-grow p-8 max-w-7xl w-full mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';

const page = usePage();

const isUrl = (url) => {
    // Exact match or prefix match
    return page.url === url || page.url.startsWith(url + '/') || (url === '/admin' && page.url === '/admin');
};

const flashSuccess = ref('');
const flashError = ref('');
const flashKey = ref(0);
let flashTimeout = null;

const updateFlash = () => {
    flashSuccess.value = page.props.flash?.success || '';
    flashError.value = page.props.flash?.error || '';
    
    if (flashSuccess.value || flashError.value) {
        if (flashTimeout) clearTimeout(flashTimeout);
        flashKey.value++; // Increment key to force re-render and restart animation
        flashTimeout = setTimeout(() => {
            clearFlash();
        }, 5000);
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

router.on('finish', () => {
    updateFlash();
});

onMounted(() => {
    updateFlash();
});

const logout = () => {
    router.post('/logout');
};
</script>
