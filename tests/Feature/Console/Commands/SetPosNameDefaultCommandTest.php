<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetPosNameDefaultCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_app_set_pos_name_default_command(): void
    {
        Item::factory()->count(2)->create();

        ItemVariant::factory()->for(Item::factory())->count(2)->create();

        $this->artisan('app:set-pos-name-default')
            ->expectsOutput('Setting default POS names for items and item variants...')
            ->expectsOutput('Default POS names have been set successfully.')
            ->assertExitCode(0);

        $this->assertCount(0, Item::whereNull('pos_name')->orWhere('pos_name', '')->get());

        $this->assertCount(0, ItemVariant::whereNull('pos_name')->orWhere('pos_name', '')->get());
    }
}
