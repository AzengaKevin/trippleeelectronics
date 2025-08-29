<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class OrganizationCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Non-Profit',
            'For-Profit',
            'Government',
            'Educational',
            'Healthcare',
            'Technology',
            'Finance',
            'Retail',
            'Manufacturing',
            'Hospitality',
        ];

        collect($categories)->each(function ($name) {

            \App\Models\OrganizationCategory::query()->updateOrCreate(compact('name'));
        });
    }
}
