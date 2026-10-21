<template>
    <div class="min-h-screen bg-gray-light text-ink-dark flex items-center justify-center p-6 font-sans">
        <div class="max-w-md w-full bg-white border border-border-soft rounded-xl p-8 space-y-6">
            <!-- Header -->
            <div class="text-center pb-4 border-b border-border-soft">
                <span class="text-[9px] font-bold uppercase tracking-widest text-white bg-ink-dark px-3 py-1 rounded-sm">
                    Xendit Simulator
                </span>
                <h1 class="text-lg font-bold text-ink-dark mt-3 uppercase tracking-wider">
                    Tagihan Pembayaran Elvara
                </h1>
                <p class="text-xs text-gray-dark mt-1">Rujukan Invoice: {{ order.order_reference }}</p>
            </div>

            <!-- Details billing -->
            <div class="bg-gray-light p-4 rounded-lg space-y-3 border border-border-soft text-xs">
                <div class="flex justify-between font-medium">
                    <span class="text-gray-dark">Metode:</span>
                    <span class="text-ink-dark font-bold">Simulasi Virtual Account / QRIS</span>
                </div>
                <div class="flex justify-between font-medium">
                    <span class="text-gray-dark">Status Tagihan:</span>
                    <span class="text-ink-dark uppercase font-bold">{{ order.status_payment }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-border-soft">
                    <span class="text-gray-dark font-semibold">Total Tagihan:</span>
                    <span class="text-sm font-bold text-ink-dark">{{ formatPrice(order.grand_total) }}</span>
                </div>
            </div>

            <p class="text-[10px] text-gray-dark leading-relaxed text-center">
                Tekan tombol di bawah untuk menyimulasikan pembayaran lunas. Sistem akan langsung memperbarui status pembayaran pesanan Anda secara real-time.
            </p>

            <!-- Simulator Action Buttons -->
            <div class="space-y-3 pt-2">
                <button 
                    @click="payNow"
                    :disabled="processing"
                    class="w-full btn-primary h-11 text-xs font-bold flex items-center justify-center cursor-pointer"
                >
                    {{ processing ? 'Memproses...' : 'Simulasikan Bayar Lunas' }}
                </button>
                
                <Link 
                    :href="`/orders/${order.order_reference}`" 
                    class="w-full btn-secondary h-11 text-xs font-bold flex items-center justify-center text-center cursor-pointer"
                >
                    Kembali & Bayar Nanti
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: {
        type: Object,
        required: true
    }
});

const processing = ref(false);

const payNow = () => {
    processing.value = true;
    router.post(`/mock-xendit-payment/${props.order.order_reference}/pay`, {}, {
        onFinish: () => {
            processing.value = false;
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
