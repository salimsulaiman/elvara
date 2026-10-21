<template>
    <AdminLayout>
        <div class="space-y-8 font-sans">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">Dashboard Utama</h1>
                    <p class="text-xs text-gray-dark mt-1">Pantau performa penjualan dan kelola pengiriman produk.</p>
                </div>
            </div>

            <!-- Metric Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Pendapatan -->
                <div class="content-card space-y-2">
                    <p class="text-[10px] font-bold text-gray-dark uppercase tracking-widest">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-ink-dark">{{ formatPrice(metrics.revenue) }}</p>
                </div>

                <!-- Jumlah Order -->
                <div class="content-card space-y-2">
                    <p class="text-[10px] font-bold text-gray-dark uppercase tracking-widest">Total Transaksi</p>
                    <p class="text-2xl font-bold text-ink-dark">{{ metrics.orders }} Pesanan</p>
                </div>

                <!-- Pengguna Terdaftar -->
                <div class="content-card space-y-2">
                    <p class="text-[10px] font-bold text-gray-dark uppercase tracking-widest">Total Pelanggan</p>
                    <p class="text-2xl font-bold text-ink-dark">{{ metrics.customers }} Akun</p>
                </div>
            </div>

            <!-- Split Panel: Best Sellers & Resi Updates -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Best Selling Products Table -->
                <div class="content-card lg:col-span-1 space-y-4 h-fit">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Produk Terlaris
                    </h3>
                    <div v-if="best_sellers.length > 0" class="space-y-3">
                        <div v-for="(item, idx) in best_sellers" :key="idx" class="flex justify-between items-center text-xs">
                            <span class="font-medium text-gray-dark truncate pr-4">{{ item.name }}</span>
                            <span class="font-bold text-ink-dark whitespace-nowrap">{{ item.total_sold }} terjual</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-4 text-xs text-gray-dark">
                        Belum ada penjualan tercatat.
                    </div>
                </div>

                <!-- Recent Transactions / Shipping updates -->
                <div class="content-card lg:col-span-2 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Transaksi Terbaru & Pengiriman
                    </h3>

                    <div v-if="recent_orders.length > 0" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-border-soft text-gray-dark font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-3 pr-4">Ref Order</th>
                                    <th class="py-3 pr-4">Pelanggan</th>
                                    <th class="py-3 pr-4">Total</th>
                                    <th class="py-3 pr-4">Pembayaran</th>
                                    <th class="py-3">Aksi Resi Pengiriman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="order in recent_orders" :key="order.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                    <td class="py-3 pr-4 font-bold text-ink-dark">{{ order.order_reference }}</td>
                                    <td class="py-3 pr-4 text-gray-dark truncate max-w-[120px]">{{ order.user?.name }}</td>
                                    <td class="py-3 pr-4 font-semibold text-ink-dark">{{ formatPrice(order.grand_total) }}</td>
                                    <td class="py-3 pr-4">
                                        <span :class="[order.status_payment === 'PAID' ? 'text-ink-dark font-bold' : 'text-gray-dark', 'uppercase text-[9px]']">
                                            {{ order.status_payment }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <!-- Update tracking number form -->
                                        <div v-if="order.status_payment === 'PAID' && !order.tracking_number" class="flex gap-2 items-center">
                                            <input 
                                                type="text" 
                                                v-model="resiInputs[order.id]"
                                                placeholder="Input No. Resi" 
                                                class="bg-gray-light text-[11px] rounded px-2.5 py-1.5 w-36 outline-none focus:bg-white border border-border-soft focus:border-border-dark"
                                            />
                                            <button 
                                                @click="submitResi(order.id)"
                                                class="btn-primary py-1 px-3 text-[10px] whitespace-nowrap h-fit rounded"
                                            >
                                                Kirim
                                            </button>
                                        </div>
                                        <span v-else-if="order.tracking_number" class="text-ink-dark font-semibold">
                                            Resi: {{ order.tracking_number }} ({{ order.status_delivery }})
                                        </span>
                                        <span v-else class="text-gray-dark italic opacity-70">
                                            Menunggu pembayaran
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-dark">
                        Belum ada pesanan masuk.
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({ revenue: 0, orders: 0, customers: 0 })
    },
    best_sellers: {
        type: Array,
        default: () => []
    },
    recent_orders: {
        type: Array,
        default: () => []
    }
});

const resiInputs = ref({});

const submitResi = (orderId) => {
    const trackingNumber = resiInputs.value[orderId];
    if (!trackingNumber) {
        alert('Masukkan nomor resi terlebih dahulu.');
        return;
    }

    router.post(`/admin/orders/${orderId}/waybill`, {
        tracking_number: trackingNumber
    }, {
        preserveScroll: true,
        onSuccess: () => {
            resiInputs.value[orderId] = '';
        }
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
