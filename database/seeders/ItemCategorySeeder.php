<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {

        $categories_data = [
            [
                'name' => 'Televisions & Home Entertainment',
                'description' => 'TVs, sound systems, and home entertainment devices.',
                'children' => [
                    [
                        'name' => 'LED & Smart TVs',
                        'description' => 'High-definition and smart televisions for home and business use.',
                    ],
                    [
                        'name' => 'Home Theatres',
                        'description' => 'Sound systems and surround setups for a cinema-like experience.',
                    ],
                    [
                        'name' => 'Decoders & TV Boxes',
                        'description' => 'DSTV, GoTV, Startimes, Zuku, and streaming boxes.',
                    ],
                ],
            ],
            [
                'name' => 'Home Appliances',
                'description' => 'Essential electronics for home and kitchen use.',
                'children' => [
                    [
                        'name' => 'Refrigerators & Freezers',
                        'description' => 'Single-door, double-door, and chest freezers.',
                    ],
                    [
                        'name' => 'Washing Machines',
                        'description' => 'Automatic and semi-automatic washing machines.',
                    ],
                    [
                        'name' => 'Microwaves & Ovens',
                        'description' => 'Microwave ovens, electric cookers, and air fryers.',
                    ],
                    [
                        'name' => 'Small Kitchen Appliances',
                        'description' => 'Blenders, kettles, rice cookers, toasters, and coffee makers.',
                    ],
                ],
            ],
            [
                'name' => 'Cameras & Accessories',
                'description' => 'Photography and videography equipment.',
                'children' => [
                    [
                        'name' => 'Digital Cameras',
                        'description' => 'Point-and-shoot and DSLR cameras.',
                    ],
                    [
                        'name' => 'Action Cameras',
                        'description' => 'Compact cameras for adventure and sports use.',
                    ],
                    [
                        'name' => 'Camera Accessories',
                        'description' => 'Tripods, lenses, batteries, and memory cards.',
                    ],
                ],
            ],
            [
                'name' => 'Audio & Sound Systems',
                'description' => 'Personal and professional sound equipment.',
                'children' => [
                    [
                        'name' => 'Speakers',
                        'description' => 'Bluetooth speakers, soundbars, and party speakers.',
                    ],
                    [
                        'name' => 'Radios',
                        'description' => 'FM/AM radios and portable radios, including solar-powered.',
                    ],
                    [
                        'name' => 'Musical Instruments & DJ Equipment',
                        'description' => 'Keyboards, mixers, amplifiers, and microphones.',
                    ],
                ],
            ],
            [
                'name' => 'Power & Electricals',
                'description' => 'Electronics for power backup and electricity use.',
                'children' => [
                    [
                        'name' => 'Generators',
                        'description' => 'Portable and standby generators for homes and businesses.',
                    ],
                    [
                        'name' => 'Solar Products',
                        'description' => 'Solar panels, solar batteries, and solar lanterns.',
                    ],
                    [
                        'name' => 'UPS & Stabilizers',
                        'description' => 'Uninterrupted power supplies and voltage stabilizers.',
                    ],
                ],
            ],
            [
                'name' => 'Personal Care Electronics',
                'description' => 'Electronic devices for grooming and personal use.',
                'children' => [
                    [
                        'name' => 'Shavers & Trimmers',
                        'description' => 'Electric shavers and hair clippers.',
                    ],
                    [
                        'name' => 'Hair Dryers & Straighteners',
                        'description' => 'Personal grooming and styling appliances.',
                    ],
                    [
                        'name' => 'Health Devices',
                        'description' => 'Blood pressure monitors, thermometers, and weighing scales.',
                    ],
                ],
            ],
        ];

        $this->add_categories($categories_data, null);
    }

    private function add_categories(array $categories, ?ItemCategory $parent = null)
    {

        collect($categories)->each(function ($item) use ($parent) {

            $name = data_get($item, 'name');
            $description = data_get($item, 'description');
            $children = data_get($item, 'children', []);

            $category = ItemCategory::firstOrCreate(
                compact('name'),
                [
                    'description' => $description,
                    'parent_id' => $parent?->id,
                ]
            );

            if (count($children)) {

                $this->add_categories($children, $category);
            }
        });
    }
}
