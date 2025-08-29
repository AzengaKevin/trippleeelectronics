<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

trait ExcelService
{
    public function robustImport($import, UploadedFile $uploadedFile, string $directory, string $resource, string $disk = 'local'): void
    {

        $filename = $uploadedFile->getClientOriginalName();

        $path = $uploadedFile->storeAs("imports/$directory", $filename, $disk);

        $import->import($path, $disk);

        if ($howMany = count($import->failures())) {

            foreach ($import->failures() as $failure) {

                if (config('app.debug')) {

                    Log::debug($failure->errors());
                }

                activity()->withProperties([
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'messages' => implode(', ', $failure->errors()),
                    'values' => $failure->values(),
                ])->log("Importing $resource, Line {$failure->row()} Failed");
            }

            throw new Exception("Importing $howMany row(s) of $resource failed, check activity logs for more details");
        }

        if ($howMany = count($import->errors())) {

            foreach ($import->errors() as $throwable) {

                if (config('app.debug')) {

                    Log::debug($throwable->getMessage());
                }

                activity()->log($throwable->getMessage());
            }

            throw new Exception("Errors importing $howMany row(s) of $resource, check activity logs for more details");
        }

    }
}
