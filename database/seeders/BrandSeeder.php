<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'name' => 'Samsung',
                'description' => 'A leading global brand known for smartphones, televisions, home appliances, and more.',
            ],
            [
                'name' => 'Apple',
                'description' => 'Renowned for premium smartphones, laptops, tablets, and smartwatches with a strong ecosystem.',
            ],
            [
                'name' => 'Sony',
                'description' => 'A pioneer in entertainment technology, including televisions, cameras, and gaming consoles.',
            ],
            [
                'name' => 'LG',
                'description' => 'Offers high-quality home appliances, OLED TVs, and mobile devices with innovative technology.',
            ],
            [
                'name' => 'Dell',
                'description' => 'A trusted name in laptops, desktops, and computer accessories for personal and business use.',
            ],
            [
                'name' => 'HP',
                'description' => 'A leading brand in laptops, printers, and computer accessories with reliable performance.',
            ],
            [
                'name' => 'Lenovo',
                'description' => 'Known for durable and innovative laptops, desktops, and tablets for all user needs.',
            ],
            [
                'name' => 'Bose',
                'description' => 'Famous for premium-quality audio products, including headphones, speakers, and sound systems.',
            ],
            [
                'name' => 'Canon',
                'description' => 'A top choice for professional and consumer cameras, printers, and imaging solutions.',
            ],
            [
                'name' => 'Microsoft',
                'description' => 'The creator of Surface laptops, Xbox gaming consoles, and software solutions for productivity.',
            ],
        ];

        collect($data)->each(fn ($item) => \App\Models\Brand::query()->create($item));

    }
}
