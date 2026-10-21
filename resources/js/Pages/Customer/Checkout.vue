<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-6 py-12 font-sans">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-4 mb-10">
                Prosedur Checkout
            </h1>

            <form @submit.prevent="submitCheckout" class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
                
                <!-- Left: Shipping & Address Form -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="content-card space-y-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                            Informasi Pengiriman
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nama Penerima</label>
                                <input type="text" v-model="form.recipient_name" required class="input-field" placeholder="Nama Lengkap" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">No. Telepon / WA</label>
                                <input type="text" v-model="form.recipient_phone" required class="input-field" placeholder="08xxxxxxxxxx" />
                            </div>
                        </div>

                        <!-- Provinces Dropdown -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Provinsi</label>
                                <select v-model="selectedProvince" @change="loadCities" required class="input-field py-0">
                                    <option value="" disabled>Pilih Provinsi</option>
                                    <option v-for="p in provincesList" :key="p.province_id" :value="p">
                                        {{ p.province }}
                                    </option>
                                </select>
                            </div>

                            <!-- Cities Dropdown -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Kabupaten / Kota</label>
                                <select v-model="selectedCity" @change="loadSubdistricts" :disabled="!selectedProvince" required class="input-field py-0">
                                    <option value="" disabled>Pilih Kota</option>
                                    <option v-for="c in citiesList" :key="c.city_id" :value="c">
                                        {{ c.type }} {{ c.city_name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Subdistricts Dropdown -->
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Kecamatan</label>
                                <select v-model="selectedSubdistrict" @change="resetShipping" :disabled="!selectedCity" required class="input-field py-0">
                                    <option value="" disabled>Pilih Kecamatan</option>
                                    <option v-for="s in subdistrictsList" :key="s.subdistrict_id" :value="s">
                                        {{ s.subdistrict_name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Address details -->
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Alamat Lengkap (Jalan, RT/RW, No. Rumah)</label>
                            <textarea v-model="form.recipient_address" required class="input-field h-24 py-3 resize-none" placeholder="Tulis alamat pengiriman secara detail..."></textarea>
                        </div>
                    </div>

                    <!-- Logistics (Courier & Shipping Rates) -->
                    <div class="content-card space-y-4">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                            Metode Pengiriman
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Pilih Kurir</label>
                                <select v-model="form.courier" @change="fetchShippingRates" :disabled="!selectedSubdistrict" required class="input-field py-0">
                                    <option value="" disabled>Pilih Kurir</option>
                                    <option value="jne">JNE (Jalur Nugraha Ekakurir)</option>
                                    <option value="pos">POS Indonesia</option>
                                    <option value="j&t">J&T Express</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Pilih Layanan</label>
                                <select v-model="selectedService" @change="applyShippingCost" :disabled="shippingRates.length === 0" required class="input-field py-0">
                                    <option value="" disabled>Pilih Layanan Kurir</option>
                                    <option v-for="(rate, idx) in shippingRates" :key="idx" :value="rate">
                                        {{ rate.service }} ({{ rate.description }}) - {{ formatPrice(rate.cost) }} ({{ rate.etd }})
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary & Checkout Button -->
                <div class="content-card space-y-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark">Ringkasan Pembayaran</h3>

                    <!-- Order items preview list -->
                    <div class="space-y-3 max-h-40 overflow-y-auto pr-1 border-b border-border-soft pb-4">
                        <div v-for="item in cart.items" :key="item.id" class="flex justify-between items-center text-xs">
                            <div class="truncate pr-4">
                                <p class="font-bold text-ink-dark truncate">{{ item.sku?.variant?.product?.name }}</p>
                                <p class="text-[10px] text-gray-dark mt-0.5">Warna: {{ item.sku?.variant?.variant_name }} | Size: {{ item.sku?.size }} (x{{ item.quantity }})</p>
                            </div>
                            <div class="text-right flex flex-col items-end flex-shrink-0">
                                <span class="font-semibold text-ink-dark">
                                    {{ formatPrice((item.sku?.discounted_price ?? item.sku?.price) * item.quantity) }}
                                </span>
                                <span v-if="item.sku?.active_discount" class="text-[9px] text-gray-dark line-through">
                                    {{ formatPrice(item.sku?.price * item.quantity) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 text-xs border-b border-border-soft pb-4">
                        <div class="flex justify-between">
                            <span class="text-gray-dark">Subtotal Produk</span>
                            <span class="font-bold text-ink-dark">{{ formatPrice(subtotal) }}</span>
                        </div>
                        <div v-if="appliedVoucher" class="flex justify-between text-ink-dark">
                            <span>Diskon Voucher ({{ appliedVoucher.code }})</span>
                            <span>-{{ formatPrice(appliedVoucher.discount_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-dark">Ongkos Kirim ({{ totalWeight }}g)</span>
                            <span class="font-bold text-ink-dark">{{ formatPrice(form.shipping_cost) }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between text-sm font-bold">
                            <span class="text-ink-dark">Total Pembayaran</span>
                            <span class="text-ink-dark">{{ formatPrice(grandTotal) }}</span>
                        </div>

                        <button 
                            type="submit"
                            :disabled="checkoutProcessing || form.shipping_cost === 0"
                            class="w-full btn-primary h-11 text-center flex items-center justify-center font-bold text-xs disabled:opacity-50"
                        >
                            {{ checkoutProcessing ? 'Memproses Pesanan...' : 'Bayar Sekarang' }}
                        </button>
                        
                        <p v-if="form.shipping_cost === 0" class="text-[10px] text-center text-gray-dark">
                            Pilih kurir & layanan pengiriman untuk melakukan pembayaran.
                        </p>
                    </div>
                </div>

            </form>
        </div>
    </CustomerLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    cart: {
        type: Object,
        required: true
    },
    provinces: {
        type: Array,
        default: () => []
    }
});

const checkoutProcessing = ref(false);
const provincesList = ref(props.provinces);
const citiesList = ref([]);
const subdistrictsList = ref([]);
const shippingRates = ref([]);

const selectedProvince = ref('');
const selectedCity = ref('');
const selectedSubdistrict = ref('');
const selectedService = ref('');
const appliedVoucher = ref(null);

const form = reactive({
    recipient_name: '',
    recipient_phone: '',
    recipient_address: '',
    province: '',
    city: '',
    subdistrict: '',
    subdistrict_id: '',
    courier: '',
    service: '',
    shipping_cost: 0,
    voucher_code: ''
});

const subtotal = computed(() => {
    if (!props.cart.items) return 0;
    return props.cart.items.reduce((sum, item) => {
        return sum + ((item.sku?.discounted_price ?? item.sku?.price) * item.quantity);
    }, 0);
});

const totalWeight = computed(() => {
    if (!props.cart.items) return 0;
    return props.cart.items.reduce((sum, item) => {
        const itemWeight = item.sku?.variant?.product?.weight || 500;
        return sum + (itemWeight * item.quantity);
    }, 0);
});

const grandTotal = computed(() => {
    const discount = appliedVoucher.value ? appliedVoucher.value.discount_amount : 0;
    const total = (subtotal.value - discount) + form.shipping_cost;
    return total < 0 ? 0 : total;
});

// Load cities when province changes
const loadCities = async () => {
    citiesList.value = [];
    subdistrictsList.value = [];
    selectedCity.value = '';
    selectedSubdistrict.value = '';
    resetShipping();

    if (!selectedProvince.value) return;

    form.province = selectedProvince.value.province;

    try {
        const response = await fetch(`/api/cities/${selectedProvince.value.province_id}`);
        const result = await response.json();
        if (result.status === 'success') {
            citiesList.value = result.data;
        }
    } catch (e) {
        console.error('Load cities error:', e);
    }
};

// Load subdistricts when city changes
const loadSubdistricts = async () => {
    subdistrictsList.value = [];
    selectedSubdistrict.value = '';
    resetShipping();

    if (!selectedCity.value) return;

    form.city = `${selectedCity.value.type} ${selectedCity.value.city_name}`;

    try {
        const response = await fetch(`/api/subdistricts/${selectedCity.value.city_id}`);
        const result = await response.json();
        if (result.status === 'success') {
            subdistrictsList.value = result.data;
        }
    } catch (e) {
        console.error('Load subdistricts error:', e);
    }
};

const resetShipping = () => {
    shippingRates.value = [];
    selectedService.value = '';
    form.courier = '';
    form.service = '';
    form.shipping_cost = 0;
    form.subdistrict = selectedSubdistrict.value ? selectedSubdistrict.value.subdistrict_name : '';
    form.subdistrict_id = selectedSubdistrict.value ? selectedSubdistrict.value.subdistrict_id : '';
};

// Fetch shipping costs via RajaOngkir API
const fetchShippingRates = async () => {
    shippingRates.value = [];
    selectedService.value = '';
    form.service = '';
    form.shipping_cost = 0;

    if (!form.courier || !selectedSubdistrict.value) return;

    try {
        const response = await fetch('/api/shipping-cost', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                destination_id: selectedSubdistrict.value.subdistrict_id,
                weight: totalWeight.value,
                courier: form.courier
            })
        });

        const result = await response.json();
        if (result.status === 'success' && result.data.length > 0) {
            const courierResult = result.data[0];
            const rates = [];
            courierResult.costs?.forEach(item => {
                rates.push({
                    service: item.service,
                    description: item.description,
                    cost: item.cost[0]?.value || 0,
                    etd: item.cost[0]?.etd || '2-3 Hari'
                });
            });
            shippingRates.value = rates;
        }
    } catch (e) {
        console.error('Fetch shipping cost error:', e);
    }
};

const applyShippingCost = () => {
    if (!selectedService.value) return;
    form.service = selectedService.value.service;
    form.shipping_cost = selectedService.value.cost;
};

const submitCheckout = () => {
    checkoutProcessing.value = true;
    
    // Add voucher code if any
    form.voucher_code = appliedVoucher.value ? appliedVoucher.value.code : null;

    router.post('/checkout', form, {
        onFinish: () => {
            checkoutProcessing.value = false;
            // Clear voucher cache after successful submit
            sessionStorage.removeItem('applied_voucher');
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

onMounted(() => {
    // Check if there is an applied voucher from Cart
    const cached = sessionStorage.getItem('applied_voucher');
    if (cached) {
        try {
            appliedVoucher.value = JSON.parse(cached);
        } catch (e) {
            sessionStorage.removeItem('applied_voucher');
        }
    }
});
</script>
