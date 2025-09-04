<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\BlockOutOfContractEmployee;
use App\Http\Middleware\BlockSuspendedEmployee;
use App\Models\Allocation;
use App\Models\Enums\ReservationStatus;
use App\Models\Individual;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {

    $reservation = Reservation::query()->create([
        'author_user_id' => User::query()->first()->id,
        'property_id' => Property::query()->first()->id,
        'primary_individual_id' => Individual::inRandomOrder()->first()->id,
        'status' => ReservationStatus::RESERVED,
        'checkin_date' => now()->toDateString(),
        'checkout_date' => now()->toDateString(),
        'adults' => 3,
    ]);

    $allocation = Allocation::query()->create([
        'reservation_id' => $reservation->id,
        'property_id' => $reservation->property_id,
        'room_id' => Room::query()->inRandomOrder()->first()->id,
        'date' => now()->toDateString(),
        'start' => now()->setTime(16, 0, 0)->toDateTimeString(),
        'end' => now()->setTime(18, 0, 0)->toDateTimeString(),
    ]);

    dd($allocation);
})->name('test');

Route::get('/', WelcomeController::class)->name('welcome');
Route::get('/about-us', [PageController::class, 'aboutUs'])->name('about-us');
Route::get('/cart', [PageController::class, 'cart'])->name('cart');
Route::get('/claiming-warranty', [PageController::class, 'claimingWarranty'])->name('claiming-warranty');
Route::get('/contact-us', [PageController::class, 'contactUs'])->name('contact-us');
Route::post('/contact-us', [PageController::class, 'contactUsStore'])->name('contact-us');
Route::get('/placing-order', [PageController::class, 'placingOrder'])->name('placing-order');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/refund-policy', [PageController::class, 'refundPolicy'])->name('refund-policy');
Route::get('/return-policy', [PageController::class, 'returnPolicy'])->name('return-policy');
Route::get('/services', [PageController::class, 'services'])->name('services');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');
Route::get('/track-your-order', [PageController::class, 'trackYourOrder'])->name('track-your-order');
Route::get('/wishlist', [PageController::class, 'wishlist'])->name('wishlist');

Route::get('/orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.process');
Route::get('/checkout/{order}/order-received', [CheckoutController::class, 'orderReceived'])->name('checkout.order-received');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::prefix('/backoffice')->as('backoffice.')->middleware(['auth', 'can:access-backoffice', BlockSuspendedEmployee::class, BlockOutOfContractEmployee::class])->group(base_path('routes/backoffice.php'));

Route::prefix('/account')->as('account.')->middleware(['auth'])->group(base_path('routes/account.php'));

Route::get('/{slug}', [ProductCategoryController::class, 'index'])->name('products.categories.index');
