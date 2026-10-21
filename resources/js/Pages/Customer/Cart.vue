<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 font-sans">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-4 mb-10">
                Keranjang Belanja
            </h1>

            <div v-if="cart.items && cart.items.length > 0" class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                
                <!-- Left: Cart Items List -->
                <div class="lg:col-span-2 space-y-6">
                    <div 
                        v-for="item in cart.items" 
                        :key="item.id"
                        class="flex items-center gap-6 border border-border-soft rounded-lg p-4 bg-white"
                    >
                        <!-- Image representation -->
                        <div class="w-20 h-20 rounded-md bg-gray-light overflow-hidden flex-shrink-0">
                            <img 
                                :src="item.sku?.variant?.variant_image || item.sku?.variant?.product?.cover_image" 
                                :alt="item.sku?.variant?.product?.name"
                                class="w-full h-full object-cover"
                            />
                        </div>

                        <!-- Info details -->
                        <div class="flex-grow min-w-0">
                            <Link :href="`/product/${item.sku?.variant?.product?.slug}`" class="text-sm font-bold text-ink-dark hover:underline truncate block">
                                {{ item.sku?.variant?.product?.name }}
                            </Link>
                            <p class="text-xs text-gray-dark mt-0.5">
                                Warna: {{ item.sku?.variant?.variant_name }} | Ukuran: {{ item.sku?.size }}
                            </p>
                            <div class="mt-1 flex items-baseline gap-2 flex-wrap">
                                <template v-if="item.sku?.active_discount">
                                    <span class="text-xs font-bold text-ink-dark">
                                        {{ formatPrice(item.sku?.discounted_price) }}
                                    </span>
                                    <span class="text-[10px] text-gray-dark line-through">
                                        {{ formatPrice(item.sku?.price) }}
                                    </span>
                                    <span class="text-[8px] bg-rose-50 border border-rose-200 text-rose-600 font-bold uppercase px-1 rounded-sm">
                                        Promo
                                    </span>
                                </template>
                                <template v-else>
                                    <span class="text-xs font-bold text-ink-dark">
                                        {{ formatPrice(item.sku?.price) }}
                                    </span>
                                </template>
                            </div>
                        </div>

                        <!-- Actions: Quantity controls & Delete -->
                        <div class="flex items-center gap-4 flex-shrink-0">
                            <!-- Quantity adjuster -->
                            <div class="flex items-center border border-border-soft rounded-md bg-gray-light h-9">
                                <button 
                                    @click="updateQty(item, item.quantity - 1)" 
                                    :disabled="item.quantity <= 1 || processingItem === item.id"
                                    class="px-2.5 h-full hover:bg-border-soft text-xs font-bold cursor-pointer disabled:opacity-30"
                                >
                                    &#8722;
                                </button>
                                <span class="px-3 text-xs font-bold text-ink-dark select-none">
                                    {{ item.quantity }}
                                </span>
                                <button 
                                    @click="updateQty(item, item.quantity + 1)" 
                                    :disabled="item.quantity >= item.sku?.stock || processingItem === item.id"
                                    class="px-2.5 h-full hover:bg-border-soft text-xs font-bold cursor-pointer disabled:opacity-30"
                                >
                                    &#43;
                                </button>
                            </div>

                            <!-- Delete button -->
                            <button 
                                @click="deleteItem(item)" 
                                class="p-2 text-gray-dark hover:text-ink-dark cursor-pointer transition-colors"
                                title="Hapus item"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary Sidebar -->
                <div class="content-card space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark">Ringkasan Belanja</h3>

                    <div class="space-y-3 text-xs border-b border-border-soft pb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-dark">Subtotal</span>
                            <span class="font-bold text-ink-dark">{{ formatPrice(subtotal) }}</span>
                        </div>
                        <div v-if="appliedVoucher" class="flex justify-between text-ink-dark">
                            <span>Diskon Voucher ({{ appliedVoucher.code }})</span>
                            <span>-{{ formatPrice(appliedVoucher.discount_amount) }}</span>
                        </div>
                    </div>

                    <!-- Voucher Promo input -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider block">Gunakan Kode Voucher</label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                v-model="voucherCode"
                                placeholder="CONTOH: ELVARANEW" 
                                class="bg-gray-light text-ink-dark text-xs rounded-md px-3 py-2 flex-grow uppercase outline-none focus:bg-white focus:border focus:border-border-dark"
                                :disabled="appliedVoucher !== null"
                            />
                            <button 
                                v-if="appliedVoucher === null"
                                @click="applyVoucher" 
                                :disabled="!voucherCode || voucherProcessing"
                                class="btn-primary text-xs py-2 px-4 whitespace-nowrap disabled:opacity-50"
                            >
                                Gunakan
                            </button>
                            <button 
                                v-else
                                @click="removeVoucher" 
                                class="btn-secondary text-xs py-2 px-4 whitespace-nowrap"
                            >
                                Hapus
                            </button>
                        </div>
                        <p v-if="voucherMessage" class="text-[10px] font-medium text-ink-dark mt-1">
                            {{ voucherMessage }}
                        </p>
                    </div>

                    <div class="space-y-4 pt-2">
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-ink-dark">Total Tagihan</span>
                            <span class="text-ink-dark">{{ formatPrice(grandTotal) }}</span>
                        </div>
                        
                        <button 
                            @click="proceedToCheckout"
                            class="w-full btn-primary h-11 text-center flex items-center justify-center font-bold text-xs"
                        >
                            Lanjut Ke Checkout
                        </button>
                    </div>
                </div>

            </div>

            <!-- Empty Cart state -->
            <div v-else class="text-center py-20 bg-gray-light rounded-xl space-y-4">
                <p class="text-sm font-bold text-ink-dark">Keranjang Belanja Anda Kosong</p>
                <p class="text-xs text-gray-dark max-w-sm mx-auto leading-relaxed">
                    Anda belum memasukkan produk apapun ke dalam keranjang. Mulai jelajahi katalog koleksi pakaian minimalis kami.
                </p>
                <div class="pt-2">
                    <Link href="/catalog" class="btn-primary text-xs">Jelajahi Katalog</Link>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

// ... (keep the rest unchanged until formatPrice)

const props = defineProps({
    cart: {
        type: Object,
        default: () => ({})
    }
});

const processingItem = ref(null);
const voucherCode = ref('');
const voucherProcessing = ref(false);
const voucherMessage = ref('');
const appliedVoucher = ref(null);

const subtotal = computed(() => {
    if (!props.cart.items) return 0;
    return props.cart.items.reduce((sum, item) => {
        return sum + ((item.sku?.discounted_price ?? item.sku?.price) * item.quantity);
    }, 0);
});

const grandTotal = computed(() => {
    const discount = appliedVoucher.value ? appliedVoucher.value.discount_amount : 0;
    const total = subtotal.value - discount;
    return total < 0 ? 0 : total;
});

const updateQty = (item, newQty) => {
    processingItem.value = item.id;
    router.patch(`/cart/${item.id}`, {
        quantity: newQty
    }, {
        preserveScroll: true,
        onFinish: () => {
            processingItem.value = null;
        }
    });
};

const deleteItem = (item) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        router.delete(`/cart/${item.id}`, {
            preserveScroll: true
        });
    }
};

const applyVoucher = async () => {
    if (!voucherCode.value) return;

    voucherProcessing.value = true;
    voucherMessage.value = '';

    try {
        const response = await fetch('/checkout/apply-voucher', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                code: voucherCode.value,
                subtotal: subtotal.value
            })
        });

        const result = await response.json();

        if (response.ok && result.status === 'success') {
            appliedVoucher.value = result.data;
            voucherMessage.value = `Voucher berhasil digunakan! Potongan harga sebesar ${formatPrice(result.data.discount_amount)}.`;
            
            // Store applied voucher session or pass state to next checkout page
            sessionStorage.setItem('applied_voucher', JSON.stringify(result.data));
        } else {
            voucherMessage.value = result.message || 'Gagal memverifikasi voucher.';
        }
    } catch (error) {
        console.error('Apply voucher exception:', error);
        voucherMessage.value = 'Terjadi kesalahan sistem saat mencoba menerapkan voucher.';
    } finally {
        voucherProcessing.value = false;
    }
};

const removeVoucher = () => {
    appliedVoucher.value = null;
    voucherCode.value = '';
    voucherMessage.value = '';
    sessionStorage.removeItem('applied_voucher');
};

const proceedToCheckout = () => {
    // If a voucher is applied, store it in sessionStorage so Checkout can load it
    router.visit('/checkout');
};

const formatPrice = (value) => {
    if (!value) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

// Check if a voucher was already applied on page load
onMounted(() => {
    const cached = sessionStorage.getItem('applied_voucher');
    if (cached) {
        try {
            const parsed = JSON.parse(cached);
            appliedVoucher.value = parsed;
            voucherCode.value = parsed.code;
            voucherMessage.value = `Voucher ${parsed.code} aktif.`;
        } catch (e) {
            sessionStorage.removeItem('applied_voucher');
        }
    }
});
</script>
