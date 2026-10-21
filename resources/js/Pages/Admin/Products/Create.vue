<template>
    <AdminLayout>
        <div class="space-y-8 font-sans pb-16">
            <!-- Header section -->
            <div class="border-b border-border-soft pb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold uppercase tracking-wider text-ink-dark">
                        {{ is_editing ? 'Edit & Matrix Setup' : 'Buat Produk Baru' }}
                    </h1>
                    <p class="text-xs text-gray-dark mt-1">
                        {{ is_editing ? 'Konfigurasi varian visual warna dan matrix detail SKU produk.' : 'Buat produk master utama terlebih dahulu.' }}
                    </p>
                </div>
                <Link href="/admin/products" class="btn-secondary py-2 px-4 text-xs font-semibold">
                    &larr; Kembali
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                
                <!-- Left Panel: Product Master Form -->
                <div class="content-card space-y-4 lg:col-span-1">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark border-b border-border-soft pb-2">
                        Data Utama Produk
                    </h3>

                    <form @submit.prevent="submitProduct" class="space-y-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nama Produk</label>
                            <input type="text" v-model="productForm.name" required placeholder="Contoh: Kaos Katun Minimal" class="input-field" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Kategori</label>
                            <select v-model="productForm.category_id" required class="input-field py-0">
                                <option value="" disabled>Pilih Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Berat Produk (gram)</label>
                            <input type="number" v-model="productForm.weight" required min="1" class="input-field" />
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Deskripsi Lengkap</label>
                            <textarea v-model="productForm.description" required class="input-field h-36 py-3 resize-none" placeholder="Tulis deskripsi detail produk..."></textarea>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Status Aktif</label>
                            <select v-model="productForm.is_active" required class="input-field py-0">
                                <option :value="1">Aktif (Tampil di Toko)</option>
                                <option :value="0">Nonaktif (Sembunyikan)</option>
                            </select>
                        </div>

                        <button type="submit" :disabled="productProcessing" class="w-full btn-primary py-2.5 text-xs">
                            {{ is_editing ? 'Simpan Data Utama' : 'Buat Produk Master' }}
                        </button>
                    </form>
                </div>

                <!-- Right Panel: Variant & SKU Matrix Generator (Only when editing) -->
                <div class="lg:col-span-2 space-y-6">
                    <div v-if="is_editing" class="space-y-6">
                        
                        <!-- Variant Management section -->
                        <div class="content-card space-y-4">
                            <div class="border-b border-border-soft pb-2 flex justify-between items-center">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-ink-dark">
                                    1. Tambah Varian Warna Baru
                                </h3>
                            </div>

                            <form @submit.prevent="submitVariant" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end bg-gray-light/35 p-4 rounded-lg border border-border-soft">
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Nama Warna</label>
                                    <input type="text" v-model="variantForm.variant_name" required placeholder="Contoh: Midnight Black" class="input-field h-9 text-xs py-1" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[10px] font-bold text-ink-dark uppercase tracking-wider">Foto Warna</label>
                                    <input type="file" @change="handleVariantImage" accept="image/*" class="text-xs text-gray-dark file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-ink-dark file:text-white hover:file:opacity-90 cursor-pointer" />
                                </div>
                                <button type="submit" :disabled="variantProcessing" class="btn-primary h-9 py-0 text-xs font-bold flex items-center justify-center">
                                    {{ variantProcessing ? 'Menyimpan...' : 'Tambah Varian' }}
                                </button>
                            </form>
                        </div>

                        <!-- Varian & SKU list -->
                        <div class="space-y-6">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-ink-dark">
                                2. Matrix Varian & Detail SKU
                            </h3>

                            <div v-if="product.variants && product.variants.length > 0" class="space-y-6">
                                <div 
                                    v-for="v in product.variants" 
                                    :key="v.id"
                                    class="content-card border border-border-soft space-y-4"
                                >
                                    <!-- Variant header info -->
                                    <div class="flex justify-between items-center border-b border-border-soft pb-3">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded border border-border-soft bg-gray-light overflow-hidden">
                                                <img :src="v.variant_image" class="w-full h-full object-cover" />
                                            </div>
                                            <div>
                                                <h4 class="text-xs font-bold text-ink-dark uppercase">{{ v.variant_name }}</h4>
                                                <p class="text-[9px] text-gray-dark font-mono mt-0.5">Varian ID: {{ v.id }}</p>
                                            </div>
                                        </div>
                                        
                                        <button @click="deleteVariant(v)" class="text-xs text-gray-dark hover:text-ink-dark cursor-pointer">
                                            Hapus Varian
                                        </button>
                                    </div>

                                    <!-- SKU size matrix items -->
                                    <div class="space-y-3">
                                        <h5 class="text-[10px] font-bold uppercase text-ink-dark tracking-wider">Ukuran / SKU Terdaftar</h5>
                                        
                                        <!-- Matrix table -->
                                        <div v-if="v.skus && v.skus.length > 0" class="overflow-x-auto">
                                            <table class="w-full text-left border-collapse text-[11px]">
                                                <thead>
                                                    <tr class="border-b border-border-soft text-gray-dark font-bold">
                                                        <th class="py-2 pr-4">Kode SKU</th>
                                                        <th class="py-2 pr-4">Ukuran</th>
                                                        <th class="py-2 pr-4">Harga Satuan</th>
                                                        <th class="py-2 pr-4">Stok</th>
                                                        <th class="py-2 pr-4">Diskon Tempel</th>
                                                        <th class="py-2 text-right">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="sku in v.skus" :key="sku.id" class="border-b border-border-soft hover:bg-gray-light/35">
                                                        <td class="py-2 pr-4 font-mono font-bold text-ink-dark">{{ sku.sku }}</td>
                                                        <td class="py-2 pr-4 text-gray-dark">{{ sku.size }}</td>
                                                        <td class="py-2 pr-4 font-semibold text-ink-dark">
                                                            <div class="flex flex-col">
                                                                <span :class="{'line-through text-gray-dark text-[10px]': sku.active_discount}">{{ formatPrice(sku.price) }}</span>
                                                                <span v-if="sku.active_discount" class="text-ink-dark font-bold">{{ formatPrice(sku.discounted_price) }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="py-2 pr-4 text-gray-dark">{{ sku.stock }} item</td>
                                                        <td class="py-2 pr-4">
                                                            <select 
                                                                :value="sku.discounts && sku.discounts.length > 0 ? sku.discounts[0].id : ''"
                                                                @change="updateSkuDiscount(sku, $event.target.value)"
                                                                class="bg-white text-[10px] rounded px-1.5 py-1 border border-border-soft focus:border-border-dark outline-none cursor-pointer"
                                                            >
                                                                <option value="">Tanpa Diskon</option>
                                                                <option 
                                                                    v-for="d in discounts" 
                                                                    :key="d.id" 
                                                                    :value="d.id"
                                                                >
                                                                    {{ d.title }} ({{ d.type === 'percentage' ? `${d.value}%` : formatPrice(d.value) }})
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td class="py-2 text-right">
                                                            <button @click="deleteSku(sku)" class="text-gray-dark hover:text-ink-dark cursor-pointer font-semibold">
                                                                Hapus
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <p v-else class="text-[11px] text-gray-dark italic">
                                            Belum ada ukuran SKU untuk varian warna ini. Silakan input di bawah.
                                        </p>

                                        <!-- Form to add SKU to this variant -->
                                        <form v-if="skuForms[v.id]" @submit.prevent="submitSku(v)" class="flex flex-wrap sm:flex-nowrap gap-3 items-end bg-gray-light/25 p-3 rounded border border-border-soft/60">
                                            <div class="space-y-1 flex-grow">
                                                <label class="text-[9px] font-bold text-ink-dark uppercase tracking-wider block">Ukuran (Size)</label>
                                                <input type="text" v-model="skuForms[v.id].size" required placeholder="Contoh: S, M, 41" class="bg-white text-[11px] rounded px-2 py-1.5 w-full outline-none focus:border focus:border-border-dark border border-border-soft" />
                                            </div>
                                            <div class="space-y-1 flex-grow">
                                                <label class="text-[9px] font-bold text-ink-dark uppercase tracking-wider block">Harga Jual (Rp)</label>
                                                <input type="number" v-model="skuForms[v.id].price" required class="bg-white text-[11px] rounded px-2 py-1.5 w-full outline-none focus:border focus:border-border-dark border border-border-soft" />
                                            </div>
                                            <div class="space-y-1 flex-grow">
                                                <label class="text-[9px] font-bold text-ink-dark uppercase tracking-wider block">Stok Awal</label>
                                                <input type="number" v-model="skuForms[v.id].stock" required class="bg-white text-[11px] rounded px-2 py-1.5 w-full outline-none focus:border focus:border-border-dark border border-border-soft" />
                                            </div>
                                            <button type="submit" class="btn-primary py-1.5 px-3 text-[10px] h-fit whitespace-nowrap">
                                                + Tambah SKU
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 bg-gray-light rounded-lg">
                                <p class="text-xs text-gray-dark font-medium">Belum ada variasi warna ditambahkan. Silakan tambahkan pada form di atas.</p>
                            </div>
                        </div>

                    </div>
                    
                    <!-- Prompt to create master first -->
                    <div v-else class="content-card text-center py-20 bg-gray-light/50 border-dashed space-y-2">
                        <p class="text-xs font-bold text-ink-dark">Varian & SKU Matrix Generator</p>
                        <p class="text-[11px] text-gray-dark max-w-sm mx-auto leading-relaxed">
                            Formulir matriks SKU warna dan ukuran akan terbuka secara otomatis di kolom kanan setelah Anda berhasil menyimpan Data Utama Produk di samping.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    product: {
        type: Object,
        default: () => null
    },
    categories: {
        type: Array,
        default: () => []
    },
    discounts: {
        type: Array,
        default: () => []
    },
    is_editing: {
        type: Boolean,
        default: false
    }
});

const productProcessing = ref(false);
const variantProcessing = ref(false);
const skuForms = ref({});

watch(() => props.product?.variants, (variants) => {
    if (variants) {
        variants.forEach(v => {
            if (!skuForms.value[v.id]) {
                skuForms.value[v.id] = {
                    size: '',
                    price: '',
                    stock: ''
                };
            }
        });
    }
}, { immediate: true, deep: true });

// Product Form
const productForm = reactive({
    category_id: '',
    name: '',
    description: '',
    weight: 500,
    is_active: 1
});

// Color Variant Form
const variantForm = reactive({
    variant_name: '',
    variant_image: null
});

// Init forms on mounted
onMounted(() => {
    if (props.is_editing && props.product) {
        productForm.category_id = props.product.category_id;
        productForm.name = props.product.name;
        productForm.description = props.product.description;
        productForm.weight = props.product.weight;
        productForm.is_active = props.product.is_active ? 1 : 0;

        // Init SKU form objects for each variant
        props.product.variants?.forEach(v => {
            skuForms.value[v.id] = {
                size: '',
                price: '',
                stock: ''
            };
        });
    }
});

// Submit Product Master Info
const submitProduct = () => {
    productProcessing.value = true;
    
    if (props.is_editing) {
        router.put(`/admin/products/${props.product.id}`, productForm, {
            onFinish: () => {
                productProcessing.value = false;
            }
        });
    } else {
        router.post('/admin/products', productForm, {
            onFinish: () => {
                productProcessing.value = false;
            }
        });
    }
};

// Handle Color Variant image select
const handleVariantImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        variantForm.variant_image = file;
    }
};

// Submit Color Variant
const submitVariant = () => {
    if (!props.product) return;
    
    variantProcessing.value = true;

    // Use FormData for image file upload
    const formData = new FormData();
    formData.append('variant_name', variantForm.variant_name);
    if (variantForm.variant_image) {
        formData.append('variant_image', variantForm.variant_image);
    }

    router.post(`/admin/products/${props.product.id}/variants`, formData, {
        onSuccess: () => {
            variantForm.variant_name = '';
            variantForm.variant_image = null;
        },
        onFinish: () => {
            variantProcessing.value = false;
        }
    });
};

const deleteVariant = (variant) => {
    if (confirm(`Apakah Anda yakin ingin menghapus varian warna "${variant.variant_name}" beserta seluruh SKU di dalamnya?`)) {
        router.delete(`/admin/variants/${variant.id}`);
    }
};

// Submit SKU Size item
const submitSku = (variant) => {
    const skuForm = skuForms.value[variant.id];
    if (!skuForm.size || !skuForm.price || !skuForm.stock) {
        alert('Lengkapi seluruh data SKU ukuran.');
        return;
    }

    router.post(`/admin/variants/${variant.id}/skus`, {
        size: skuForm.size,
        price: skuForm.price,
        stock: skuForm.stock
    }, {
        onSuccess: () => {
            skuForm.size = '';
            skuForm.price = '';
            skuForm.stock = '';
        }
    });
};

const deleteSku = (sku) => {
    if (confirm(`Apakah Anda yakin ingin menghapus SKU ukuran "${sku.size}" ini?`)) {
        router.delete(`/admin/skus/${sku.id}`);
    }
};

const updateSkuDiscount = (sku, discountId) => {
    router.post(`/admin/skus/${sku.id}/discount`, {
        discount_id: discountId || null
    }, {
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
</script>
