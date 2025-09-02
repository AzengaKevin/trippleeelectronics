<?php

use App\Http\Controllers\Backoffice\ActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ActivityController::class, 'index'])->middleware(['can:browse-activities'])->name('index');
Route::get('/{activity}', [ActivityController::class, 'show'])->middleware(['can:browse-activities'])->name('show');
Route::delete('/{activity}', [ActivityController::class, 'destroy'])->middleware(['can:delete-activities'])->name('destroy');
