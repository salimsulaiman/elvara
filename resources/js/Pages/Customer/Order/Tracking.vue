<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 font-sans space-y-10">
            <!-- Breadcrumbs -->
            <div class="text-xs text-gray-dark flex items-center gap-2">
                <Link href="/" class="hover:text-ink-dark">Beranda</Link>
                <span>/</span>
                <Link href="/orders" class="hover:text-ink-dark">Pesanan Saya</Link>
                <span>/</span>
                <span class="text-ink-dark font-medium">Pelacakan Order</span>
            </div>

            <!-- Page Title header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-border-soft pb-6">
                <div class="space-y-1">
                    <h1 class="text-2xl font-bold uppercase tracking-wider text-ink-dark">
                        Rincian Pesanan
                    </h1>
                    <p class="text-xs text-gray-dark font-medium">
                        Rujukan Pesanan: <span class="text-ink-dark font-bold">{{ order.order_reference }}</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <span :class="[
                        order.status_payment === 'PAID' ? 'bg-ink-dark text-white' : 'bg-gray-light text-gray-dark border border-border-soft',
                        'px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider'
                    ]">
                        Pembayaran: {{ order.status_payment }}
                    </span>
                    <span :class="[
                        order.status_delivery === 'DELIVERED' ? 'bg-ink-dark text-white' : 'bg-gray-light text-gray-dark border border-border-soft',
                        'px-4 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider'
                    ]">
                        Status Kurir: {{ order.status_delivery }}
                    </span>
                </div>
            </div>

            <!-- Main grid split -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                
                <!-- Left: Order Details & Payment details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Billing information card -->
                    <div class="content-card space-y-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                            Barang yang Dibeli
                        </h3>
                        
                        <div class="space-y-4">
                            <div v-for="item in order.items" :key="item.id" class="flex gap-4 items-center">
                                <div class="w-16 h-16 rounded bg-gray-light overflow-hidden flex-shrink-0">
                                    <img 
                                        :src="item.sku?.variant?.variant_image || item.sku?.variant?.product?.cover_image" 
                                        :alt="item.sku?.variant?.product?.name"
                                        class="w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-grow text-xs space-y-1">
                                    <h4 class="font-bold text-ink-dark">{{ item.sku?.variant?.product?.name }}</h4>
                                    <p class="text-gray-dark">
                                        Warna: {{ item.sku?.variant?.variant_name }} | Ukuran: {{ item.sku?.size }}
                                    </p>
                                    <p class="text-gray-dark">
                                        Kuantitas: {{ item.quantity }} x {{ formatPrice(item.price_at_purchase) }}
                                    </p>
                                </div>
                                <span class="text-xs font-bold text-ink-dark whitespace-nowrap">
                                    {{ formatPrice(item.price_at_purchase * item.quantity) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment summary card -->
                    <div class="content-card space-y-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                            Alamat Pengiriman & Kurir
                        </h3>
                        <div class="text-xs space-y-3">
                            <div>
                                <p class="text-gray-dark">Metode Ekspedisi:</p>
                                <p class="font-bold text-ink-dark mt-0.5 uppercase">{{ order.shipping_courier }} (Layanan: {{ order.shipping_service }})</p>
                            </div>
                            <div>
                                <p class="text-gray-dark">Tujuan Alamat Lengkap:</p>
                                <p class="font-medium text-ink-dark mt-0.5 whitespace-pre-line leading-relaxed">{{ order.shipping_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Payment & Delivery Timeline Status -->
                <div class="space-y-6">
                    <!-- Action: Invoice payment button -->
                    <div v-if="order.status_payment === 'PENDING'" class="content-card bg-gray-light border-border-dark space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark">
                            Pembayaran Menunggu
                        </h3>
                        <p class="text-xs text-gray-dark leading-relaxed">
                            Silakan lakukan pembayaran sebesar **{{ formatPrice(order.grand_total) }}** untuk memproses pesanan Anda.
                        </p>
                        
                        <a 
                            :href="order.xendit_invoice_url" 
                            class="w-full btn-primary h-11 text-xs font-bold text-center flex items-center justify-center"
                        >
                            Bayar Sekarang (Xendit)
                        </a>
                    </div>

                    <!-- Checkout costs breakdown -->
                    <div class="content-card space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                            Ringkasan Biaya
                        </h3>
                        <div class="space-y-2.5 text-xs">
                            <div class="flex justify-between text-gray-dark">
                                <span>Subtotal</span>
                                <span>{{ formatPrice(order.subtotal) }}</span>
                            </div>
                            <div v-if="order.discount_amount > 0" class="flex justify-between text-ink-dark font-medium">
                                <span>Diskon Voucher</span>
                                <span>-{{ formatPrice(order.discount_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-dark">
                                <span>Ongkos Kirim</span>
                                <span>{{ formatPrice(order.shipping_cost) }}</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold text-ink-dark pt-2 border-t border-border-soft">
                                <span>Grand Total</span>
                                <span>{{ formatPrice(order.grand_total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Stepper tracking -->
                    <div class="content-card space-y-4">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                            Status Pelacakan Kurir
                        </h3>

                        <!-- Waybill tracking list -->
                        <div v-if="tracking_data && tracking_data.manifest" class="space-y-6 relative pl-4 border-l border-border-soft mt-4">
                            <div v-for="(log, idx) in tracking_data.manifest" :key="idx" class="relative">
                                <!-- Dot indicator -->
                                <span :class="[
                                    idx === 0 ? 'bg-ink-dark ring-4 ring-gray-light' : 'bg-border-soft',
                                    'absolute -left-[21px] top-1 w-2.5 h-2.5 rounded-full border border-white'
                                ]"></span>
                                
                                <div class="text-[11px] space-y-0.5">
                                    <p class="font-bold text-ink-dark leading-snug">{{ log.manifest_description }}</p>
                                    <p class="text-gray-dark text-[10px]">{{ log.manifest_date }} {{ log.manifest_time }} | {{ log.city_name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- No waybill yet -->
                        <div v-else class="text-center py-4 bg-gray-light rounded-lg">
                            <p class="text-[11px] font-bold text-ink-dark">Sedang Mengemas</p>
                            <p class="text-[10px] text-gray-dark max-w-xs mx-auto leading-relaxed mt-1.5 px-3">
                                Pesanan Anda sedang diproses dan dikemas oleh pihak Elvara. Nomor resi pengiriman akan segera dimasukkan oleh admin toko.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineProps({
    order: {
        type: Object,
        required: true
    },
    tracking_data: {
        type: Object,
        default: () => null
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
</script>
