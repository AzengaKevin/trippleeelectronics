<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        collect([
            [
                'name' => 'Wi-Fi',
                'description' => 'Reliable internet access for browsing, streaming, and remote work.',
            ],
            [
                'name' => 'Free parking',
                'description' => 'Secure parking space available at no extra charge.',
            ],
            [
                'name' => 'Hot shower',
                'description' => 'Modern bathroom with continuous hot water supply.',
            ],
            [
                'name' => 'Fully equipped kitchen',
                'description' => 'Kitchen with stove, utensils, and cookware for self-catering.',
            ],
            [
                'name' => 'Refrigerator',
                'description' => 'Fridge for storing food and drinks.',
            ],
            [
                'name' => 'Microwave',
                'description' => 'Convenient appliance for heating and preparing quick meals.',
            ],
            [
                'name' => 'Cooking basics',
                'description' => 'Essential items like pots, pans, oil, salt, and spices.',
            ],
            [
                'name' => 'Television',
                'description' => 'TV with local or international channels for entertainment.',
            ],
            [
                'name' => 'Streaming services',
                'description' => 'Access to Netflix, Showmax, or other on-demand platforms.',
            ],
            [
                'name' => 'Fan or Air conditioning',
                'description' => 'Cooling options for comfort during warm weather.',
            ],
            [
                'name' => 'Mosquito net',
                'description' => 'Protective netting to keep mosquitoes away while sleeping.',
            ],
            [
                'name' => 'Comfortable bedding',
                'description' => 'Quality duvets, pillows, and clean linens provided.',
            ],
            [
                'name' => 'Laundry facilities',
                'description' => 'Washer (and sometimes dryer) for guests to do laundry.',
            ],
            [
                'name' => 'Iron and ironing board',
                'description' => 'Tools for keeping clothes neat and wrinkle-free.',
            ],
            [
                'name' => 'Work desk / Workspace',
                'description' => 'Dedicated area suitable for remote work or studying.',
            ],
            [
                'name' => 'Swimming pool access',
                'description' => 'On-site or shared pool available for relaxation and exercise.',
            ],
            [
                'name' => 'Security',
                'description' => '24/7 guard, CCTV, or gated compound for guest safety.',
            ],
            [
                'name' => 'Outdoor seating / Balcony',
                'description' => 'Space to relax outdoors with fresh air and views.',
            ],
            [
                'name' => 'Breakfast or tea/coffee facilities',
                'description' => 'Basic provisions or appliances for making tea, coffee, or light breakfast.',
            ],
            [
                'name' => 'First aid kit',
                'description' => 'Emergency medical kit available for guest safety.',
            ],
        ])->each(function ($item) {

            $attributes = [
                'name' => data_get($item, 'name'),
            ];

            $values = [
                'description' => data_get($item, 'description'),
            ];

            Amenity::query()->updateOrCreate($attributes, $values);
        });
    }
}
