# Agent Specification: Multicategory E-Commerce Brand Platform (RajaOngkir & Xendit)

Dokumen ini berfungsi sebagai panduan utama pengembangan platform e-commerce multicategory menggunakan stack **Laravel 11, Vue 3 (Composition API), dan Laravel Inertia.js** dengan integrasi **Xendit** dan **RajaOngkir**.

---

## 1. Business Requirements Document (BRD)

### 1.1 Objective & Scope
Membentuk platform e-commerce terpadu satu merek (monobrand) multi-kategori dengan manajemen inventaris tingkat lanjut. Data varian (Warna/Koleksi) dipisahkan dari ukuran (Size) untuk menghasilkan manajemen SKU yang profesional, akurat, dan dinamis.

### 1.2 Core Features & Requirements

#### A. Advanced Catalog, Variant, & Size System
* **Multi-Category:** Produk dikelompokkan ke dalam kategori induk secara dinamis (seperti Pakaian, Sepatu, Jaket).
* **Normalized Variant & Size:** * Satu produk memiliki beberapa **Varian** (Warna/Model visual). Foto spesifik diikat di sini.
    * Setiap Varian memiliki beberapa **Ukuran** (Size).
    * Kombinasi `Product` + `Variant` + `Size` menghasilkan satu **SKU** dengan stok dan harga tersendiri.
* **Product Galleries:** Setiap produk mendukung banyak foto tambahan sebagai galeri umum di luar foto varian.

#### B. Shopping Cart & Wishlist System
* **Database-Backed Cart:** Keranjang belanja persisten disimpan di database (`cart_items` terikat ke `product_skus`). Mendukung checkout massal lintas kategori.

#### C. Authentication & Security
* **Single Table Auth:** 1 tabel `users` dengan kolom `role` (`admin`, `customer`).
* **OAuth Login:** Login via Google (Laravel Socialite).
* **Account Security:** Fitur Lupa Password dan verifikasi kode OTP ke email.
* **Minimalist UI Profile:** Otomatisasi inisial nama tanpa upload avatar.

#### D. Logistics & Payment Gateways
* **RajaOngkir Pro API:** Perhitungan ongkir otomatis secara *real-time* berbasis kecamatan tujuan dan akumulasi berat total (`gram`) dari isi keranjang belanja.
* **In-App Live Tracking:** Pelacakan status nomor resi secara langsung (*real-time*) di halaman riwayat order.
* **Xendit Integration:** Pembayaran otomatis (VA, QRIS, E-Wallet) dikawal dengan sistem aman **Webhook Handler**.

#### E. Marketing & Content Management
* **Voucher & Discount Engine:** Pembatasan expired, minimal order, kuota batas global, serta kuota maksimal per user (cth: 1 akun max 1x pakai).
* **Banner Management:** Pengaturan banner promo di landing page yang dapat diurutkan.

---

## 2. Navigation & Interface Architecture

### 2.1 Customer Navbar Structure
Komponen Navbar pada `CustomerLayout.vue` didesain bersih, sinematik, dan responsif:
1. **Brand Logo:** Sisi kiri (Arah tautan ke Home).
2. **Dynamic Categories Menu:** Diambil langsung dari database via Inertia shared data.
    * **Pakaian** (Link ke `/catalog?category=pakaian`)
    * **Sepatu** (Link ke `/catalog?category=sepatu`)
    * **Jaket** (Link ke `/catalog?category=jaket`)
3. **Search Bar:** Input pencarian produk interaktif (Full-text search ke `/catalog?search=...`).
4. **Action Icons (Sisi Kanan):**
    * **Wishlist Icon:** Menampilkan badge jumlah item disukai.
    * **Cart Icon:** Menampilkan badge kuantitas item aktif (di-update real-time lewat state/Inertia share).
    * **User Profile/Auth Dropdown:** * *Kondisi Belum Login:* Menampilkan tombol "Masuk / Daftar".
        * *Kondisi Sudah Login:* Menampilkan inisial nama bulat dengan dropdown menu: `Riwayat Pesanan`, `Keluar (Logout)`.

---

### 2.2 Customer Frontend Sections Detail

#### A. Halaman Beranda (Home.vue)
* **Section 1: Hero Banner Carousel:** Menampilkan banner promo dinamis, mendukung navigasi klik langsung menuju halaman produk/kategori tertentu.
* **Section 2: Category Highlights:** Grid visual ikonik yang memetakan kategori utama (Pakaian, Sepatu, Jaket) menggunakan depth-of-field image background.
* **Section 3: New Arrivals / Featured Products:** Menampilkan grid produk terbaru dengan info harga rentang terbawah hingga tertinggi dari varian produk tersebut.

#### B. Halaman Katalog Toko (Catalog.vue)
* **Section Left: Advanced Filter Sidebar:** Filter berdasarkan kategori, rentang harga (min-max), dan status diskon.
* **Section Right: Product Grid Display:** Menampilkan kartu produk dengan fitur *quick-view* wishlist, judul produk, nama kategori, dan label harga.

#### C. Halaman Detail Produk (Product/Show.vue)
* **Section Left: Media Gallery:** Komponen *swiper/thumbnail slider* foto galeri utama + transisi otomatis ke foto varian jika warna dipilih.
* **Section Right: Product Configurator:**
    * Detail nama produk, deskripsi tekstual, informasi berat produk.
    * **Variant Selector:** Pilihan bulatan warna (Varian) interaktif.
    * **Size Selector:** Menampilkan ukuran yang tersedia khusus dari varian warna terpilih.
    * **Dynamic Pricing Display:** Harga berubah mengikuti SKU kombinasi warna & ukuran yang dipilih user.
    * **Actions:** Tombol "Tambah ke Keranjang" dan "Tambah ke Wishlist".

#### D. Halaman Keranjang Belanja (Cart.vue)
* **Section Left: Cart Items List:** Daftar seluruh SKU yang siap dibeli. Setiap baris menampilkan foto varian, nama produk, ukuran, harga satuan SKU, kontrol kuantitas (+ / -), dan tombol hapus.
* **Section Right: Order Summary Preview:** Menampilkan kalkulasi awal Subtotal produk sebelum ongkir, kolom input kode voucher promo, dan tombol "Lanjut ke Checkout".

#### E. Halaman Prosedur Checkout (Checkout.vue)
* **Section Left: Shipping & Address Information:** Form pilihan Provinsi, Kota/Kabupaten, dan Kecamatan berbasis data master RajaOngkir, pengisian alamat detail, serta dropdown pilihan kurir (JNE, POS, J&T) beserta layanan ongkirnya (REG, YES, OKE).
* **Section Right: Order Billing Breakdown:** Menampilkan ringkasan produk yang dibeli, harga ongkir final, potongan diskon voucher jika valid, total pembayaran bersih, serta tombol konfirmasi "Bayar Sekarang".

#### F. Halaman Riwayat & Pelacakan (Order/Tracking.vue)
* **Section 1: Order Meta & Payment Link:** Nomor referensi order, status pembayaran (`PENDING`/`PAID`), status kurir, serta link/tombol bayar Xendit (jika belum kedaluwarsa).
* **Section 2: Live Logistic Stepper:** Integrasi tracking nomor resi RajaOngkir, menampilkan timeline perjalanan kurir secara runut (contoh: Manifested $\rightarrow$ On Process $\rightarrow$ Delivered).

---

### 2.3 Admin Panel Layout & Menu Sections

#### A. Dashboard Utama
* **Section 1: Metric Cards:** Total pendapatan, jumlah order masuk, total pengguna terdaftar, dan produk terlaris.
* **Section 2: Recent Transactions:** Tabel pesanan terbaru yang membutuhkan pemrosesan pengiriman/input nomor resi.

#### B. Manajemen Katalog (Categories & Products)
* **Section Kategori:** Tabel CRUD sederhana untuk menambah nama kategori dan slug.
* **Section Master Produk:** Form penulisan nama produk, deskripsi, berat dasar, dan relasi kategori.
* **Section Variant & SKU Matrix (Professional UI):** * Form input varian warna beserta unggah foto utama warna tersebut.
    * Tabel dinamis yang men-generate baris ukuran (Size), input harga berbeda per baris, dan stok untuk tiap kombinasi SKU.

#### C. Manajemen Promo (Vouchers & Banners)
* **Section Voucher:** Form pembuatan kode kupon, tipe diskon, nominal, input tanggal aktif/kedaluwarsa, input kuota batas global, dan batas penggunaan per user.
* **Section Banner:** Drag-and-drop upload gambar banner promo berdimensi wide beserta input link tujuannya.

---

## 3. Database Schema (Normalized & Fixed)

+-------------+     +------------------+     +-------------------+
|  products   |---< | product_variants |---< |   product_skus    |
+-------------+     +------------------+     +-------------------+
|                           | (Terikat ke
v                           v  Cart & Order)
+------------------+     +-------------------+
| product_galleries|     |    cart_items     |
+------------------+     +-------------------+


### 3.1 Users, Categories, & Shopping Cart
```sql
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password')->nullable(); 
    $table->enum('role', ['admin', 'customer'])->default('customer');
    $table->string('provider_id')->nullable(); 
    $table->string('provider_name')->nullable();
    $table->string('otp_code')->nullable();
    $table->timestamp('otp_expires_at')->nullable();
    $table->timestamps();
});

Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name'); 
    $table->string('slug')->unique();
    $table->string('description')->nullable();
    $table->timestamps();
});

Schema::create('carts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

Schema::create('cart_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cart_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_sku_id')->constrained()->onDelete('cascade'); 
    $table->integer('quantity')->default(1);
    $table->timestamps();
});
3.2 Product, Variant, and Normalized SKU Tables
SQL
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->onDelete('restrict');
    $table->string('name'); 
    $table->string('slug')->unique();
    $table->text('description');
    $table->integer('weight')->default(500); 
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('product_variants', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('variant_name'); 
    $table->string('variant_image')->nullable(); 
    $table->timestamps();
});

Schema::create('product_skus', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_variant_id')->constrained()->onDelete('cascade');
    $table->string('sku')->unique(); 
    $table->string('size'); 
    $table->decimal('price', 12, 2); 
    $table->integer('stock')->default(0);
    $table->timestamps();
});

Schema::create('product_galleries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->string('image_path');
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
3.3 Marketing & Orders Tables
SQL
Schema::create('vouchers', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->enum('type', ['fixed', 'percentage']);
    $table->decimal('reward_value', 12, 2);
    $table->decimal('min_order_amount', 12, 2)->default(0); 
    $table->integer('global_limit')->nullable(); 
    $table->integer('global_used')->default(0);
    $table->integer('per_user_limit')->default(1); 
    $table->timestamp('start_date')->nullable();
    $table->timestamp('end_date')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('voucher_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('voucher_id')->constrained();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('order_id')->constrained();
    $table->timestamps();
});

Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->string('order_reference')->unique(); 
    $table->foreignId('user_id')->constrained();
    $table->decimal('subtotal', 12, 2);
    $table->decimal('discount_amount', 12, 2)->default(0);
    $table->decimal('shipping_cost', 12, 2);
    $table->decimal('grand_total', 12, 2);
    $table->string('status_payment')->default('PENDING'); 
    $table->string('status_delivery')->default('UNSHIPPED'); 
    $table->string('shipping_courier'); 
    $table->string('shipping_service'); 
    $table->string('tracking_number')->nullable(); 
    $table->text('shipping_address'); 
    $table->string('rajaongkir_destination_id'); 
    $table->string('xendit_invoice_url')->nullable();
    $table->timestamps();
});

Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_sku_id')->constrained(); 
    $table->integer('quantity');
    $table->decimal('price_at_purchase', 12, 2); 
    $table->timestamps();
});

Schema::create('wishlists', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});

Schema::create('banners', function (Blueprint $table) {
    $table->id();
    $table->string('title')->nullable();
    $table->string('image_path');
    $table->string('link_url')->nullable(); 
    $table->integer('order_position')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
4. Project Directory Structure
Plaintext
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── ProductController.php       
│   │   │   │   ├── VoucherController.php
│   │   │   │   └── BannerController.php
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php       
│   │   │   │   └── PasswordResetController.php 
│   │   │   └── Customer/
│   │   │       ├── CatalogController.php       
│   │   │       ├── CartController.php         
│   │   │       ├── CheckoutController.php     
│   │   │       └── OrderController.php        
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php      
│   └── Services/
│       ├── XenditService.php                  
│       └── RajaOngkirService.php              
│
├── resources/
│   └── js/
│       ├── Layouts/
│       │   ├── AdminLayout.vue
│       │   └── CustomerLayout.vue             
│       └── Pages/
│           ├── Admin/
│           │   ├── Categories/
│           │   ├── Products/                  
│           │   └── Vouchers/
│           ├── Auth/
│           └── Customer/
│               ├── Home.vue                   
│               ├── Catalog.vue                
│               ├── Cart.vue                   
│               ├── Product/
│               │   └── Show.vue               
│               ├── Checkout.vue               
│               └── Order/
│                   ├── Index.vue              
│                   └── Tracking.vue