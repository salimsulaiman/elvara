<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductSku;
use App\Models\Banner;
use App\Models\Voucher;
use App\Models\Discount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        User::create([
            'name' => 'Elvara Admin',
            'email' => 'admin@elvara.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Elvara Customer',
            'email' => 'customer@elvara.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        // 2. Categories
        $pakaian = Category::create([
            'name' => 'Pakaian',
            'slug' => 'pakaian',
            'description' => 'Koleksi pakaian minimalis modern dari Elvara.',
        ]);

        $sepatu = Category::create([
            'name' => 'Sepatu',
            'slug' => 'sepatu',
            'description' => 'Alas kaki berdesain kontemporer dan kenyamanan ekstra.',
        ]);

        $jaket = Category::create([
            'name' => 'Jaket',
            'slug' => 'jaket',
            'description' => 'Jaket premium pelindung cuaca dengan estetika bersih.',
        ]);

        // 3. Products, Variants & SKUs
        // Product 1: T-Shirt
        $tshirt = Product::create([
            'category_id' => $pakaian->id,
            'name' => 'Elvara Classic Tee',
            'slug' => 'elvara-classic-tee',
            'description' => 'Kaos katun premium berukuran santai dengan potongan leher kru klasik. Dibuat dari 100% katun organik dengan ketebalan sedang untuk kenyamanan pemakaian sehari-hari.',
            'weight' => 250,
            'is_active' => true,
        ]);

        $tsBlack = ProductVariant::create([
            'product_id' => $tshirt->id,
            'variant_name' => 'Midnight Black',
            'variant_image' => '/images/pakaian_tshirt_black.png',
        ]);

        ProductSku::create([
            'product_variant_id' => $tsBlack->id,
            'sku' => 'ELV-TEE-BLK-S',
            'size' => 'S',
            'price' => 249000,
            'stock' => 20,
        ]);
        ProductSku::create([
            'product_variant_id' => $tsBlack->id,
            'sku' => 'ELV-TEE-BLK-M',
            'size' => 'M',
            'price' => 249000,
            'stock' => 15,
        ]);
        ProductSku::create([
            'product_variant_id' => $tsBlack->id,
            'sku' => 'ELV-TEE-BLK-L',
            'size' => 'L',
            'price' => 249000,
            'stock' => 10,
        ]);

        $tsWhite = ProductVariant::create([
            'product_id' => $tshirt->id,
            'variant_name' => 'Pure White',
            'variant_image' => '/images/pakaian_tshirt_white.png',
        ]);

        ProductSku::create([
            'product_variant_id' => $tsWhite->id,
            'sku' => 'ELV-TEE-WHT-S',
            'size' => 'S',
            'price' => 249000,
            'stock' => 12,
        ]);
        ProductSku::create([
            'product_variant_id' => $tsWhite->id,
            'sku' => 'ELV-TEE-WHT-M',
            'size' => 'M',
            'price' => 249000,
            'stock' => 8,
        ]);
        ProductSku::create([
            'product_variant_id' => $tsWhite->id,
            'sku' => 'ELV-TEE-WHT-L',
            'size' => 'L',
            'price' => 249000,
            'stock' => 5,
        ]);

        // Product 2: Sneakers
        $sneakers = Product::create([
            'category_id' => $sepatu->id,
            'name' => 'Elvara Urban Sneakers',
            'slug' => 'elvara-urban-sneakers',
            'description' => 'Sneakers dengan gaya retro-modern yang dirancang untuk kehidupan perkotaan. Sol luar karet tahan lama dan insole empuk untuk kenyamanan sepanjang hari.',
            'weight' => 900,
            'is_active' => true,
        ]);

        $snBlack = ProductVariant::create([
            'product_id' => $sneakers->id,
            'variant_name' => 'Stealth Black',
            'variant_image' => '/images/sepatu_sneakers_black.png',
        ]);

        ProductSku::create([
            'product_variant_id' => $snBlack->id,
            'sku' => 'ELV-SNK-BLK-40',
            'size' => '40',
            'price' => 899000,
            'stock' => 5,
        ]);
        ProductSku::create([
            'product_variant_id' => $snBlack->id,
            'sku' => 'ELV-SNK-BLK-41',
            'size' => '41',
            'price' => 899000,
            'stock' => 8,
        ]);
        ProductSku::create([
            'product_variant_id' => $snBlack->id,
            'sku' => 'ELV-SNK-BLK-42',
            'size' => '42',
            'price' => 899000,
            'stock' => 6,
        ]);

        $snWhite = ProductVariant::create([
            'product_id' => $sneakers->id,
            'variant_name' => 'Minimal White',
            'variant_image' => '/images/sepatu_sneakers_white.png',
        ]);

        ProductSku::create([
            'product_variant_id' => $snWhite->id,
            'sku' => 'ELV-SNK-WHT-40',
            'size' => '40',
            'price' => 899000,
            'stock' => 4,
        ]);
        ProductSku::create([
            'product_variant_id' => $snWhite->id,
            'sku' => 'ELV-SNK-WHT-41',
            'size' => '41',
            'price' => 899000,
            'stock' => 7,
        ]);
        ProductSku::create([
            'product_variant_id' => $snWhite->id,
            'sku' => 'ELV-SNK-WHT-42',
            'size' => '42',
            'price' => 899000,
            'stock' => 5,
        ]);

        // Product 3: Varsity Jacket
        $jacket = Product::create([
            'category_id' => $jaket->id,
            'name' => 'Elvara Tech Varsity Jacket',
            'slug' => 'elvara-tech-varsity-jacket',
            'description' => 'Jaket varsity klasik yang dirancang ulang dengan material tahan air dan detail jahitan premium. Menghadirkan keseimbangan antara kehangatan dan ketahanan cuaca.',
            'weight' => 750,
            'is_active' => true,
        ]);

        $jkBlack = ProductVariant::create([
            'product_id' => $jacket->id,
            'variant_name' => 'Matte Black',
            'variant_image' => '/images/jaket_varsity_black.png',
        ]);

        ProductSku::create([
            'product_variant_id' => $jkBlack->id,
            'sku' => 'ELV-JKT-BLK-M',
            'size' => 'M',
            'price' => 599000,
            'stock' => 10,
        ]);
        ProductSku::create([
            'product_variant_id' => $jkBlack->id,
            'sku' => 'ELV-JKT-BLK-L',
            'size' => 'L',
            'price' => 599000,
            'stock' => 7,
        ]);
        ProductSku::create([
            'product_variant_id' => $jkBlack->id,
            'sku' => 'ELV-JKT-BLK-XL',
            'size' => 'XL',
            'price' => 619000,
            'stock' => 4,
        ]);

        // 4. Banners
        Banner::create([
            'title' => 'ELVARA COLLECTION 2026',
            'image_path' => '/images/banner_promo_1.png',
            'link_url' => '/catalog',
            'order_position' => 1,
            'is_active' => true,
        ]);

        // 5. Vouchers
        Voucher::create([
            'code' => 'ELVARANEW',
            'type' => 'fixed',
            'reward_value' => 50000,
            'min_order_amount' => 200000,
            'global_limit' => 100,
            'global_used' => 0,
            'per_user_limit' => 1,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        Voucher::create([
            'code' => 'ELVARA10',
            'type' => 'percentage',
            'reward_value' => 10, // 10%
            'min_order_amount' => 150000,
            'global_limit' => 200,
            'global_used' => 0,
            'per_user_limit' => 1,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // 6. Discounts
        $endOfYearDiscount = Discount::create([
            'title' => 'Diskon Akhir Tahun',
            'type' => 'percentage',
            'value' => 15,
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Attach discount to specific SKUs
        $skuTee = ProductSku::where('sku', 'ELV-TEE-BLK-M')->first();
        if ($skuTee) {
            $skuTee->discounts()->attach($endOfYearDiscount->id);
        }

        $skuSneaker = ProductSku::where('sku', 'ELV-SNK-WHT-41')->first();
        if ($skuSneaker) {
            $skuSneaker->discounts()->attach($endOfYearDiscount->id);
        }
    }
}
