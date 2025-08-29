<?php

namespace Tests\Feature\Imports;

use App\Imports\UserImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserImportTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_import_users()
    {
        $filepath = base_path('test-data/users.xlsx');

        app(UserImport::class)->import($filepath);

        $this->assertEquals(2, User::query()->count());
    }
}
