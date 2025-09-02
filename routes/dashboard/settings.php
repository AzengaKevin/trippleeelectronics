<?php

use App\Http\Controllers\Backoffice\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/settings', [SettingsController::class, 'show'])->middleware(['can:manage-settings'])->name('show');
Route::patch('/settings', [SettingsController::class, 'update'])->middleware(['can:manage-settings'])->name('update');
