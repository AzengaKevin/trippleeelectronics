<?php

use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\POSController;
use App\Http\Controllers\Backoffice\POSStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/pos', [POSController::class, 'show'])->middleware(['can:access-pos'])->name('pos');
Route::post('/pos', [POSController::class, 'process'])->middleware(['can:access-pos'])->name('pos');
Route::post('/pos/store/update', [POSStoreController::class, 'update'])->middleware(['can:access-pos'])->name('pos.store.update');

Route::prefix('users')->as('users.')->group(base_path('routes/dashboard/users.php'));
Route::prefix('item-categories')->as('item-categories.')->group(base_path('routes/dashboard/item-categories.php'));
Route::prefix('brands')->as('brands.')->group(base_path('routes/dashboard/brands.php'));
Route::prefix('services')->as('services.')->group(base_path('routes/dashboard/services.php'));
Route::prefix('properties')->as('properties.')->group(base_path('routes/dashboard/properties.php'));
Route::prefix('stores')->as('stores.')->group(base_path('routes/dashboard/stores.php'));
Route::prefix('items')->as('items.')->group(base_path('routes/dashboard/items.php'));
Route::prefix('item-variants')->as('item-variants.')->group(base_path('routes/dashboard/item-variants.php'));
Route::prefix('individuals')->as('individuals.')->group(base_path('routes/dashboard/individuals.php'));
Route::prefix('stock-levels')->as('stock-levels.')->group(base_path('routes/dashboard/stock-levels.php'));
Route::prefix('stock-movements')->as('stock-movements.')->group(base_path('routes/dashboard/stock-movements.php'));
Route::prefix('purchases')->as('purchases.')->group(base_path('routes/dashboard/purchases.php'));
Route::prefix('orders')->as('orders.')->group(base_path('routes/dashboard/orders.php'));
Route::prefix('quotations')->as('quotations.')->group(base_path('routes/dashboard/quotations.php'));
Route::prefix('permissions')->as('permissions.')->group(base_path('routes/dashboard/permissions.php'));
Route::prefix('roles')->as('roles.')->group(base_path('routes/dashboard/roles.php'));
Route::prefix('resources')->as('resources.')->group(base_path('routes/dashboard/resources.php'));
Route::prefix('organization-categories')->as('organization-categories.')->group(base_path('routes/dashboard/organization-categories.php'));
Route::prefix('organizations')->as('organizations.')->group(base_path('routes/dashboard/organizations.php'));
Route::prefix('carousels')->as('carousels.')->group(base_path('routes/dashboard/carousels.php'));
Route::prefix('settings')->as('settings.')->group(base_path('routes/dashboard/settings.php'));
Route::prefix('payments')->as('payments.')->group(base_path('routes/dashboard/payments.php'));
Route::prefix('transactions')->as('transactions.')->group(base_path('routes/dashboard/transactions.php'));
Route::prefix('payment-methods')->as('payment-methods.')->group(base_path('routes/dashboard/payment-methods.php'));
Route::prefix('notifications')->as('notifications.')->group(base_path('routes/dashboard/notifications.php'));
Route::prefix('agreements')->as('agreements.')->group(base_path('routes/dashboard/agreements.php'));
Route::prefix('contacts')->as('contacts.')->group(base_path('routes/dashboard/contacts.php'));
Route::prefix('reports')->as('reports.')->group(base_path('routes/dashboard/reports.php'));
Route::prefix('activities')->as('activities.')->group(base_path('routes/dashboard/activities.php'));
Route::prefix('messages')->as('messages.')->group(base_path('routes/dashboard/messages.php'));
Route::prefix('employees')->as('employees.')->group(base_path('routes/dashboard/employees.php'));
Route::prefix('employment')->as('employment.')->group(base_path('routes/dashboard/employment.php'));
Route::prefix('contracts')->as('contracts.')->group(base_path('routes/dashboard/contracts.php'));
Route::prefix('suspensions')->as('suspensions.')->group(base_path('routes/dashboard/suspensions.php'));
Route::prefix('media')->as('media.')->group(base_path('routes/dashboard/media.php'));
Route::prefix('accounting')->as('accounting.')->group(base_path('routes/dashboard/accounting.php'));
Route::prefix('taxes')->as('taxes.')->group(base_path('routes/dashboard/taxes.php'));
