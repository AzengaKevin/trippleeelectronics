<?php

use App\Http\Controllers\Backoffice\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NotificationController::class, 'index'])->name('index');
Route::patch('/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
