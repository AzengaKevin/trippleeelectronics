<?php

namespace Tests\Feature\Traits;

use Illuminate\Support\Facades\Storage;

trait WithTestImports
{
    public function createTestCsvFile(string $filename, array $data): string
    {
        Storage::makeDirectory('test-imports');

        $path = Storage::path("test-imports/{$filename}");

        $file = fopen($path, 'w');

        foreach ($data as $row) {
            fputcsv($file, array_values($row));
        }

        fclose($file);

        return $path;
    }

    public function deleteTestCsvFile(string $filename): string
    {
        $path = Storage::path("test-imports/{$filename}");

        if (Storage::exists("test-imports/{$filename}")) {

            Storage::delete("test-imports/{$filename}");
        }

        if (file_exists($path)) {

            unlink($path);
        }

        return $path;
    }
}
