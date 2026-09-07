<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\RestaurantTable;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'hugo@bitesrush.com'],
            [
                'name' => 'Hugo Putra',
                'username' => 'hugo',
                'phone_number' => '081234567890',
                'role' => 'admin',
                'address' => 'Jl. Boulevard Utama No. 1, Jakarta',
                'password' => Hash::make('Password123'),
            ]
        );

        $staff = User::firstOrCreate(
            ['email' => 'kasir@bitesrush.com'],
            [
                'name' => 'Kasir BiteRush',
                'username' => 'kasir_utama',
                'phone_number' => '081298765432',
                'role' => 'staff',
                'address' => 'Outlet BiteRush Pusat',
                'password' => Hash::make('Password123'),
            ]
        );

        $customer = User::firstOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'username' => 'budisantoso',
                'phone_number' => '081377889900',
                'role' => 'customer',
                'address' => 'Jl. Mawar Indah No. 12, Jakarta Barat',
                'password' => Hash::make('Password123'),
            ]
        );

        // 2. Restaurant Tables
        $tables = [];
        for ($i = 1; $i <= 10; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            $tables[] = RestaurantTable::firstOrCreate(
                ['table_number' => "Meja $num"],
                [
                    'capacity' => ($i % 3 === 0) ? 6 : (($i % 2 === 0) ? 4 : 2),
                    'qr_code' => "QR-TABLE-$num",
                    'status' => ($i === 2) ? 'occupied' : 'available',
                ]
            );
        }

        // 3. Coupons
        Coupon::firstOrCreate(
            ['code' => 'WELCOMEBITE'],
            [
                'discount_type' => 'fixed',
                'discount_value' => 15000,
                'min_spend' => 40000,
                'max_discount' => 15000,
                'valid_from' => now()->subDays(1),
                'valid_until' => now()->addMonths(6),
                'usage_limit' => 500,
                'usage_count' => 12,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'BITERUSH50'],
            [
                'discount_type' => 'percentage',
                'discount_value' => 50,
                'min_spend' => 60000,
                'max_discount' => 30000,
                'valid_from' => now()->subDays(1),
                'valid_until' => now()->addMonths(3),
                'usage_limit' => 200,
                'usage_count' => 45,
                'is_active' => true,
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'CHEESEFEAST'],
            [
                'discount_type' => 'fixed',
                'discount_value' => 20000,
                'min_spend' => 80000,
                'max_discount' => 20000,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addMonths(1),
                'usage_limit' => 100,
                'usage_count' => 8,
                'is_active' => true,
            ]
        );

        // 4. Categories
        $catBurgers = Category::firstOrCreate(
            ['slug' => 'burgers'],
            [
                'name' => 'Signature Burgers',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $catCombos = Category::firstOrCreate(
            ['slug' => 'combos'],
            [
                'name' => 'Rush Combos',
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        $catChicken = Category::firstOrCreate(
            ['slug' => 'chicken'],
            [
                'name' => 'Crispy Chicken',
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        $catSides = Category::firstOrCreate(
            ['slug' => 'sides'],
            [
                'name' => 'Sides & Snacks',
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        $catDrinks = Category::firstOrCreate(
            ['slug' => 'drinks'],
            [
                'name' => 'Drinks & Shakes',
                'is_active' => true,
                'sort_order' => 5,
            ]
        );

        $catDesserts = Category::firstOrCreate(
            ['slug' => 'desserts'],
            [
                'name' => 'Sweet Desserts',
                'is_active' => true,
                'sort_order' => 6,
            ]
        );

        // 5. Products & Product Options
        // Product 1: Ultimate Double Cheeseburger
        $p1 = Product::firstOrCreate(
            ['slug' => 'ultimate-double-cheeseburger'],
            [
                'category_id' => $catBurgers->id,
                'name' => 'Ultimate Double Cheeseburger',
                'description' => 'Dua lapis beef patty juicy, double keju cheddar leleh, caramelized onion, dan saus spesial BiteRush dalam brioche bun panggang.',
                'price' => 55000,
                'original_price' => 65000,
                'image' => 'images/burger-bg.jpg',
                'is_available' => true,
                'is_featured' => true,
                'calories' => 780,
                'prep_time_minutes' => 10,
            ]
        );

        $optPatty = ProductOption::firstOrCreate(
            ['product_id' => $p1->id, 'name' => 'Pilihan Patty'],
            ['type' => 'single', 'is_required' => true]
        );
        ProductOptionValue::firstOrCreate(['product_option_id' => $optPatty->id, 'name' => 'Double Patty (Standard)'], ['additional_price' => 0, 'is_default' => true]);
        ProductOptionValue::firstOrCreate(['product_option_id' => $optPatty->id, 'name' => 'Triple Patty (+1 Patty)'], ['additional_price' => 18000, 'is_default' => false]);

        $optTopping = ProductOption::firstOrCreate(
            ['product_id' => $p1->id, 'name' => 'Ekstra Topping'],
            ['type' => 'multiple', 'is_required' => false]
        );
        ProductOptionValue::firstOrCreate(['product_option_id' => $optTopping->id, 'name' => 'Extra Melted Cheddar'], ['additional_price' => 6000, 'is_default' => false]);
        ProductOptionValue::firstOrCreate(['product_option_id' => $optTopping->id, 'name' => 'Crispy Smoked Beef Bacon'], ['additional_price' => 8000, 'is_default' => false]);
        ProductOptionValue::firstOrCreate(['product_option_id' => $optTopping->id, 'name' => 'Pickled Jalapenos'], ['additional_price' => 4000, 'is_default' => false]);

        // Product 2: Truffle Mushroom Beef Burger
        $p2 = Product::firstOrCreate(
            ['slug' => 'truffle-mushroom-beef-burger'],
            [
                'category_id' => $catBurgers->id,
                'name' => 'Truffle Mushroom Beef Burger',
                'description' => 'Patty daging sapi panggang dengan sauteed champignon mushrooms, keju Swiss meleleh, dan saus truffle mayo aromatik.',
                'price' => 62000,
                'original_price' => 70000,
                'image' => 'images/burger-bg-2.jpg',
                'is_available' => true,
                'is_featured' => true,
                'calories' => 710,
                'prep_time_minutes' => 12,
            ]
        );

        // Product 3: Spicy Crunchy Chicken Burger
        $p3 = Product::firstOrCreate(
            ['slug' => 'spicy-crunchy-chicken-burger'],
            [
                'category_id' => $catBurgers->id,
                'name' => 'Spicy Crunchy Chicken Burger',
                'description' => 'Paha ayam fillet goreng renyah ekstra pedas, selada segar, coleslaw creamy, dan saus peri-peri.',
                'price' => 42000,
                'original_price' => 48000,
                'image' => 'images/burger-bg-3.jpg',
                'is_available' => true,
                'is_featured' => false,
                'calories' => 620,
                'prep_time_minutes' => 8,
            ]
        );

        $optSpicy = ProductOption::firstOrCreate(
            ['product_id' => $p3->id, 'name' => 'Level Kepedasan'],
            ['type' => 'single', 'is_required' => true]
        );
        ProductOptionValue::firstOrCreate(['product_option_id' => $optSpicy->id, 'name' => 'Mild (Level 1)'], ['additional_price' => 0, 'is_default' => true]);
        ProductOptionValue::firstOrCreate(['product_option_id' => $optSpicy->id, 'name' => 'Hot Rush (Level 2)'], ['additional_price' => 0, 'is_default' => false]);
        ProductOptionValue::firstOrCreate(['product_option_id' => $optSpicy->id, 'name' => 'Inferno Dare (Level 3)'], ['additional_price' => 3000, 'is_default' => false]);

        // Product 4: Super Combo Feast
        $p4 = Product::firstOrCreate(
            ['slug' => 'super-combo-feast'],
            [
                'category_id' => $catCombos->id,
                'name' => 'Super Combo Feast',
                'description' => 'Paket lengkap 1 Ultimate Double Cheeseburger + Large Golden Fries + 1 BiteRush Soft Drink 16oz.',
                'price' => 69000,
                'original_price' => 88000,
                'image' => 'images/burger-bg.jpg',
                'is_available' => true,
                'is_featured' => true,
                'calories' => 1100,
                'prep_time_minutes' => 10,
            ]
        );

        // Product 5: Fire Wings Spicy Glaze (6 Pcs)
        Product::firstOrCreate(
            ['slug' => 'fire-wings-spicy-glaze'],
            [
                'category_id' => $catChicken->id,
                'name' => 'Fire Wings Spicy Glaze (6 Pcs)',
                'description' => 'Sayap ayam krispi berbalut saus pedas manis karamel khas BiteRush bertabur biji wijen sangrai.',
                'price' => 45000,
                'original_price' => null,
                'image' => 'images/burger-bg-2.jpg',
                'is_available' => true,
                'is_featured' => false,
                'calories' => 540,
                'prep_time_minutes' => 10,
            ]
        );

        // Product 6: Loaded Cheese & Beef Bacon Fries
        Product::firstOrCreate(
            ['slug' => 'loaded-cheese-bacon-fries'],
            [
                'category_id' => $catSides->id,
                'name' => 'Loaded Cheese & Bacon Fries',
                'description' => 'Kentang goreng renyah disiram saus keju cheddar hangat, taburan beef bacon gurih, dan daun bawang segar.',
                'price' => 32000,
                'original_price' => 38000,
                'image' => 'images/burger-bg-3.jpg',
                'is_available' => true,
                'is_featured' => true,
                'calories' => 460,
                'prep_time_minutes' => 6,
            ]
        );

        // Product 7: Classic French Fries (Large)
        Product::firstOrCreate(
            ['slug' => 'classic-french-fries-large'],
            [
                'category_id' => $catSides->id,
                'name' => 'Classic French Fries (Large)',
                'description' => 'Kentang goreng renyah dengan taburan garam laut gurih alami.',
                'price' => 22000,
                'original_price' => null,
                'image' => 'images/burger-bg.jpg',
                'is_available' => true,
                'is_featured' => false,
                'calories' => 320,
                'prep_time_minutes' => 5,
            ]
        );

        // Product 8: BiteRush Vanilla Milkshake
        Product::firstOrCreate(
            ['slug' => 'biterush-vanilla-milkshake'],
            [
                'category_id' => $catDrinks->id,
                'name' => 'BiteRush Creamy Vanilla Shake',
                'description' => 'Milkshake vanilla kental dibuat dari fresh milk dan gelato vanilla asli dengan whipped cream di atasnya.',
                'price' => 28000,
                'original_price' => null,
                'image' => 'images/burger-bg-2.jpg',
                'is_available' => true,
                'is_featured' => false,
                'calories' => 380,
                'prep_time_minutes' => 4,
            ]
        );

        // Product 9: Iced Fresh Lemon Tea
        Product::firstOrCreate(
            ['slug' => 'iced-fresh-lemon-tea'],
            [
                'category_id' => $catDrinks->id,
                'name' => 'Iced Fresh Lemon Tea',
                'description' => 'Teh hitam seduh segar berpadu dengan perasan lemon asli dan daun mint sejuk.',
                'price' => 18000,
                'original_price' => null,
                'image' => 'images/burger-bg-3.jpg',
                'is_available' => true,
                'is_featured' => false,
                'calories' => 90,
                'prep_time_minutes' => 3,
            ]
        );

        // Product 10: Choco Lava Ice Cream
        Product::firstOrCreate(
            ['slug' => 'choco-lava-ice-cream'],
            [
                'category_id' => $catDesserts->id,
                'name' => 'Choco Lava Ice Cream Cup',
                'description' => 'Warm molten chocolate cake dengan lelehan cokelat di dalam disajikan bersama satu scoop es krim vanila.',
                'price' => 26000,
                'original_price' => 30000,
                'image' => 'images/burger-bg.jpg',
                'is_available' => true,
                'is_featured' => true,
                'calories' => 350,
                'prep_time_minutes' => 6,
            ]
        );

        // 6. Sample Completed Order & Payment
        $order = Order::firstOrCreate(
            ['order_number' => 'BR-20260820-0001'],
            [
                'user_id' => $customer->id,
                'restaurant_table_id' => $tables[0]->id,
                'order_type' => 'dine_in',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'qris',
                'subtotal' => 87000,
                'tax_amount' => 8700,
                'discount_amount' => 15000,
                'total_amount' => 80700,
                'notes' => 'Tolong saus sambal diperbanyak ya.',
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone_number,
            ]
        );

        $item1 = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_id' => $p1->id],
            [
                'product_name' => $p1->name,
                'price' => 55000,
                'quantity' => 1,
                'subtotal' => 55000,
                'notes' => 'Brioche bun agak garing',
            ]
        );

        OrderItemOption::firstOrCreate(
            ['order_item_id' => $item1->id, 'option_name' => 'Pilihan Patty'],
            ['option_value_name' => 'Double Patty (Standard)', 'additional_price' => 0]
        );
        OrderItemOption::firstOrCreate(
            ['order_item_id' => $item1->id, 'option_name' => 'Ekstra Topping'],
            ['option_value_name' => 'Extra Melted Cheddar', 'additional_price' => 6000]
        );

        $item2 = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'product_name' => 'Loaded Cheese & Bacon Fries'],
            [
                'product_id' => null,
                'price' => 32000,
                'quantity' => 1,
                'subtotal' => 32000,
                'notes' => null,
            ]
        );

        Payment::firstOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_code' => 'TRX-' . strtoupper(Str::random(10)),
                'payment_method' => 'qris',
                'amount' => 80700,
                'status' => 'success',
                'payment_details' => [
                    'provider' => 'QRIS GoPay / BCA',
                    'reference_id' => 'QRIS-' . time(),
                ],
                'paid_at' => now()->subMinutes(30),
            ]
        );

        Review::firstOrCreate(
            ['user_id' => $customer->id, 'product_id' => $p1->id],
            [
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Burger paling juicy dan enak! Keju dan sausnya mantap banget!',
            ]
        );
    }
}
