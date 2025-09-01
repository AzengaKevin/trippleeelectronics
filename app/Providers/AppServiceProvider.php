<?php

namespace App\Providers;

use App\Http\Responses\CustomLoginResponse;
use App\Http\Responses\CustomLogoutResponse;
use App\Models\Order;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Observers\OrderObserver;
use App\Observers\PurchaseItemObserver;
use App\Observers\StockMovementObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {

        $this->app->instance(LoginResponse::class, new CustomLoginResponse);

        $this->app->instance(LogoutResponse::class, new CustomLogoutResponse);
    }

    public function boot(): void
    {
        $this->registerMorphMap();

        $this->registerObservers();

        $this->registerPolicies();
    }

    private function registerPolicies(): void
    {
        Gate::policy(\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class);

        Gate::policy(\Spatie\Permission\Models\Permission::class, \App\Policies\PermissionPolicy::class);
    }

    private function registerMorphMap(): void
    {

        Relation::enforceMorphMap(
            [
                'user' => \App\Models\User::class,
                'item-category' => \App\Models\ItemCategory::class,
                'brand' => \App\Models\Brand::class,
                'item' => \App\Models\Item::class,
                'store' => \App\Models\Store::class,
                'item-variant' => \App\Models\ItemVariant::class,
                'individual' => \App\Models\Individual::class,
                'stock-level' => \App\Models\StockLevel::class,
                'stock-movement' => \App\Models\StockMovement::class,
                'order' => \App\Models\Order::class,
                'order-item' => \App\Models\OrderItem::class,
                'payment' => \App\Models\Payment::class,
                'purchase' => \App\Models\Purchase::class,
                'purchase-item' => \App\Models\PurchaseItem::class,
                'role' => \Spatie\Permission\Models\Role::class,
                'permission' => \Spatie\Permission\Models\Permission::class,
                'resource' => \App\Models\Resource::class,
                'organization-category' => \App\Models\OrganizationCategory::class,
                'organization' => \App\Models\Organization::class,
                'carousel' => \App\Models\Carousel::class,
                'service' => \App\Models\Service::class,
                'custom-item' => \App\Models\CustomItem::class,
                'quotation' => \App\Models\Quotation::class,
                'quotation-item' => \App\Models\QuotationItem::class,
                'agreement' => \App\Models\Agreement::class,
                'activity' => \Spatie\Activitylog\Models\Activity::class,
                'message' => \App\Models\Message::class,
                'notification' => \Illuminate\Notifications\DatabaseNotification::class,
                'employee' => \App\Models\Employee::class,
                'contract' => \App\Models\Contract::class,
                'suspension' => \App\Models\Suspension::class,
                'media' => \Spatie\MediaLibrary\MediaCollections\Models\Media::class,
                'tax' => \App\Models\Tax::class,
                'jurisdiction' => \App\Models\Jurisdiction::class,
                'property' => \App\Models\Property::class,
                'building' => \App\Models\Building::class,
                'floor' => \App\Models\Floor::class,
                'room' => \App\Models\Room::class,
                'room-type' => \App\Models\RoomType::class,
            ]
        );
    }

    private function registerObservers(): void
    {
        StockMovement::observe(StockMovementObserver::class);

        PurchaseItem::observe(PurchaseItemObserver::class);

        Order::observe(OrderObserver::class);
    }
}
