<?php

namespace Tests\Feature\Imports;

use App\Imports\ResourceImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\WithTestImports;
use Tests\TestCase;

class ResourceImportTest extends TestCase
{
    use RefreshDatabase, WithFaker, WithTestImports;

    public function test_resources_import()
    {
        $titlesRow = [
            'name',
            'route_name',
            'icon',
            'order',
            'description',
            'is_active',
            'count',
            'required_permission',
            'morph_class',
        ];

        $data = [
            [
                'name' => 'Roles',
                'route_name' => 'backoffice.roles.index',
                'icon' => 'test-icon-1',
                'order' => 1,
                'description' => 'This is a test resource 1',
                'is_active' => true,
                'count' => 10,
                'required_permission' => 'browse-roles',
                'morph_class' => 'role',
            ],
            [
                'name' => 'Permissions',
                'route_name' => 'backoffice.permissions.index',
                'icon' => 'test-icon-2',
                'order' => 2,
                'description' => 'This is a test resource 2',
                'is_active' => true,
                'count' => 5,
                'required_permission' => 'browse-permissions',
                'morph_class' => 'permission',
            ],
            [
                'name' => 'Users',
                'route_name' => 'backoffice.users.index',
                'icon' => 'test-icon-3',
                'order' => 3,
                'description' => 'This is a test resource 3',
                'is_active' => true,
                'count' => 20,
                'required_permission' => 'browse-users',
                'morph_class' => 'user',
            ],
        ];

        $filename = str('resources')->append('-')->append(now()->format('Y-m-d'))->append('.csv')->value();

        $filePath = $this->createTestCsvFile($filename, [
            $titlesRow,
            ...$data,
        ]);

        $this->assertFileExists($filePath);

        try {

            $resourceImport = new ResourceImport;

            $resourceImport->import($filePath);
        } finally {
            collect($data)->each(function ($item) {
                $this->assertDatabaseHas('resources', [
                    'name' => data_get($item, 'name'),
                    'route_name' => data_get($item, 'route_name'),
                    'icon' => data_get($item, 'icon'),
                    'order' => data_get($item, 'order'),
                    'description' => data_get($item, 'description'),
                    'is_active' => data_get($item, 'is_active'),
                    'count' => data_get($item, 'count'),
                    'required_permission' => data_get($item, 'required_permission'),
                    'morph_class' => data_get($item, 'morph_class'),
                ]);
            });

            unlink($filePath);
        }
    }
}
