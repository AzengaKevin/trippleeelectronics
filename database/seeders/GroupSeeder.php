<?php

namespace Database\Seeders;

use App\Services\GroupService;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all the employees and put the in a group, also add the admins
        app(GroupService::class)->createAdminAndStaffGroup(updateUsers: true);

        // Create another group for only the admins
        app(GroupService::class)->createAdminGroup(updateUsers: true);

        // Create another group for ony the staff
        app(GroupService::class)->createStaffGroup(updateUsers: true);
    }
}
