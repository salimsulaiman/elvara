<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 font-sans">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-4 mb-10">
                Riwayat Pesanan Saya
            </h1>

            <div v-if="orders && orders.length > 0" class="space-y-6">
                <div 
                    v-for="order in orders" 
                    :key="order.id"
                    class="border border-border-soft rounded-lg p-6 bg-white space-y-4"
                >
                    <!-- Order Header Details -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-border-soft pb-4 text-xs">
                        <div class="space-y-1">
                            <p class="font-bold text-ink-dark">
                                Rujukan Order: {{ order.order_reference }}
                            </p>
                            <p class="text-gray-dark">
                                Tanggal Pesan: {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <!-- Payment Status Badge -->
                            <span :class="[
                                order.status_payment === 'PAID' ? 'bg-ink-dark text-white' : 'bg-gray-light text-gray-dark',
                                'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider'
                            ]">
                                Pembayaran: {{ order.status_payment }}
                            </span>
                            <!-- Delivery Status Badge -->
                            <span :class="[
                                order.status_delivery === 'DELIVERED' ? 'bg-ink-dark text-white' : 'bg-gray-light text-gray-dark',
                                'px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider'
                            ]">
                                Pengiriman: {{ order.status_delivery }}
                            </span>
                        </div>
                    </div>

                    <!-- Items Summary -->
                    <div class="space-y-3">
                        <div v-for="item in order.items" :key="item.id" class="flex gap-4 items-center">
                            <div class="w-12 h-12 rounded bg-gray-light overflow-hidden flex-shrink-0">
                                <img 
                                    :src="item.sku?.variant?.variant_image || item.sku?.variant?.product?.cover_image" 
                                    :alt="item.sku?.variant?.product?.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div class="text-xs">
                                <p class="font-bold text-ink-dark">{{ item.sku?.variant?.product?.name }}</p>
                                <p class="text-gray-dark mt-0.5">Size: {{ item.sku?.size }} (x{{ item.quantity }})</p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Footer Details -->
                    <div class="flex items-center justify-between border-t border-border-soft pt-4 text-xs">
                        <div>
                            <p class="text-gray-dark">Total Tagihan:</p>
                            <p class="font-bold text-sm text-ink-dark mt-0.5">{{ formatPrice(order.grand_total) }}</p>
                        </div>

                        <Link 
                            :href="`/orders/${order.order_reference}`"
                            class="btn-primary py-2 px-4 text-xs font-semibold text-center"
                        >
                            Lacak & Rincian
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty history state -->
            <div v-else class="text-center py-20 bg-gray-light rounded-xl space-y-4">
                <p class="text-sm font-bold text-ink-dark">Belum Ada Transaksi</p>
                <p class="text-xs text-gray-dark max-w-sm mx-auto leading-relaxed">
                    Anda belum pernah melakukan pemesanan produk di toko kami. Mulai berbelanja koleksi kaos, sepatu, dan jaket premium kami.
                </p>
                <div class="pt-2">
                    <Link href="/catalog" class="btn-primary text-xs">Jelajahi Katalog</Link>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    orders: {
        type: Array,
        default: () => []
    }
});

const formatPrice = (value) => {
    if (!value) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
