<?php

namespace Tests\Feature\Console\Commands\ItemCategories;

use App\Models\ItemCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateItemsCountCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_items_categories_manual_items_count_command()
    {
        $categories = ItemCategory::factory()->hasItems($itemsCount = 5)->create();

        $this->artisan('app:item-categories:update-items-count')
            ->expectsOutput('Item Categories Manual Items Count Updated Successfully')
            ->assertExitCode(0);

        $categories->each(function ($category) use ($itemsCount) {

            $this->assertEquals($itemsCount, $category->fresh()->items_count_manual);
        });
    }
}
