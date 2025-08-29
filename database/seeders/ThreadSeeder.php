<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Thread;
use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
{
    public function run(): void
    {

        Group::query()->get()->each(function (Group $group) {

            Thread::query()->updateOrCreate([
                'name' => $group->name,
                'group_id' => $group->id,
            ], [
                'author_user_id' => $group->users->first()?->id,
                'last_updated_at' => now()->toDateTimeString(),
            ]);
        });
    }
}
