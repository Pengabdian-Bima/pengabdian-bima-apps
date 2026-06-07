<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin
        User::create([
            'name' => 'Admin UD Flamboyan',
            'email' => 'admin@flamboyan.com',
            'phone' => '081234567890',
            'password' => Hash::make('Password123'),
            'role' => 'admin',
        ]);

        // Create sample user
        User::create([
            'name' => 'Pembeli Demo',
            'email' => 'user@demo.com',
            'phone' => '081298765432',
            'password' => Hash::make('Password123'),
            'role' => 'user',
        ]);

        // Create Categories
        $categories = [
            ['name' => 'Biskuit Original', 'slug' => 'biskuit-original', 'description' => 'Biskuit Ikan Huluu rasa original khas Danau Limboto'],
            ['name' => 'Biskuit Premium', 'slug' => 'biskuit-premium', 'description' => 'Biskuit Ikan Huluu premium dengan bahan pilihan terbaik'],
            ['name' => 'Biskuit Ekonomis', 'slug' => 'biskuit-ekonomis', 'description' => 'Biskuit Ikan Huluu ekonomis untuk semua kalangan'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create Products
        $products = [
            [
                'category_id' => 1,
                'name' => 'Biskuit Ikan Huluu Original',
                'slug' => 'biskuit-ikan-huluu-original',
                'description' => 'Biskuit Ikan Huluu Danau Limboto rasa original yang kaya protein. Dibuat dari ikan segar Danau Limboto dengan resep tradisional khas Gorontalo. Cocok untuk cemilan sehat keluarga.',
                'price' => 25000,
                'cost_price' => 15000,
                'stock' => 100,
                'min_stock' => 10,
                'weight' => 200,
                'status' => true,
            ],
            [
                'category_id' => 2,
                'name' => 'Biskuit Ikan Huluu Premium',
                'slug' => 'biskuit-ikan-huluu-premium',
                'description' => 'Biskuit Ikan Huluu Danau Limboto varian premium dengan bahan terpilih. Tekstur lebih renyah dan rasa lebih gurih. Kemasan eksklusif cocok untuk oleh-oleh.',
                'price' => 45000,
                'cost_price' => 28000,
                'stock' => 50,
                'min_stock' => 5,
                'weight' => 300,
                'status' => true,
            ],
            [
                'category_id' => 3,
                'name' => 'Biskuit Ikan Huluu Ekonomis',
                'slug' => 'biskuit-ikan-huluu-ekonomis',
                'description' => 'Biskuit Ikan Huluu Danau Limboto kemasan ekonomis. Harga terjangkau dengan kualitas tetap terjaga. Cocok untuk dijadikan stok cemilan harian.',
                'price' => 15000,
                'cost_price' => 9000,
                'stock' => 200,
                'min_stock' => 20,
                'weight' => 150,
                'status' => true,
            ],
        ];

        foreach ($products as $prod) {
            Product::create($prod);
        }
    }
}
