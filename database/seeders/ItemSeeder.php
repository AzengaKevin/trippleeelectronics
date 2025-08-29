<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Samsung Galaxy S24',
                'cost' => 80000,
                'price' => 120000,
                'description' => 'Latest flagship smartphone from Samsung with an advanced camera and AI features.',
                'category' => 'Smartphones',
                'brand' => 'Samsung',
            ],
            [
                'name' => 'Apple MacBook Air M2',
                'cost' => 150000,
                'price' => 185000,
                'description' => "Lightweight and powerful laptop with Apple's M2 chip for high performance.",
                'category' => 'Laptops',
                'brand' => 'Apple',
            ],
            [
                'name' => 'Sony Bravia 55-inch OLED TV',
                'cost' => 180000,
                'price' => 220000,
                'description' => 'Premium 4K OLED TV with stunning visuals and advanced sound technology.',
                'category' => 'Televisions',
                'brand' => 'Sony',
            ],
            [
                'name' => 'LG Soundbar SN6Y',
                'cost' => 25000,
                'price' => 35000,
                'description' => 'High-quality soundbar with deep bass and AI-powered audio enhancement.',
                'category' => 'Audio & Speakers',
                'brand' => 'LG',
            ],
            [
                'name' => 'Dell XPS 15',
                'cost' => 170000,
                'price' => 200000,
                'description' => 'Premium business and creative laptop with a 4K display and powerful internals.',
                'category' => 'Laptops',
                'brand' => 'Dell',
            ],
            [
                'name' => 'HP LaserJet Pro MFP M428fdw',
                'cost' => 40000,
                'price' => 50000,
                'description' => 'High-speed multifunction printer ideal for office use with wireless printing support.',
                'category' => 'Computer Accessories',
                'brand' => 'HP',
            ],
            [
                'name' => 'Lenovo ThinkPad X1 Carbon',
                'cost' => 140000,
                'price' => 170000,
                'description' => 'Ultra-lightweight and durable business laptop with extended battery life.',
                'category' => 'Laptops',
                'brand' => 'Lenovo',
            ],
            [
                'name' => 'Bose QuietComfort 45',
                'cost' => 35000,
                'price' => 45000,
                'description' => 'Industry-leading noise-canceling headphones with superior audio quality.',
                'category' => 'Audio & Speakers',
                'brand' => 'Bose',
            ],
            [
                'name' => 'Canon EOS R5',
                'cost' => 400000,
                'price' => 450000,
                'description' => 'Professional-grade mirrorless camera with 8K video recording capability.',
                'category' => 'Cameras',
                'brand' => 'Canon',
            ],
            [
                'name' => 'Microsoft Xbox Series X',
                'cost' => 70000,
                'price' => 85000,
                'description' => 'Powerful gaming console with ultra-fast load times and 4K gaming support.',
                'category' => 'Gaming Consoles',
                'brand' => 'Microsoft',
            ],
        ];

        collect($data)->each(function ($item) {

            $brand = Brand::query()->where('name', $item['brand'])->first();

            $category = ItemCategory::query()->where('name', $item['category'])->first();

            Item::query()->create([
                'brand_id' => $brand?->id,
                'item_category_id' => $category?->id,
                'name' => $item['name'],
                'cost' => $item['cost'],
                'price' => $item['price'],
                'description' => $item['description'],
            ]);

        });

    }
}
