<?php

namespace App\Observers;

use App\Models\Enums\OrderStatus;
use App\Models\Enums\StockMovementType;
use App\Models\Order;
use App\Models\StockMovement;
use App\Services\StockMovementService;

class OrderObserver
{
    public function created(Order $order): void
    {
        if ($order->order_status === OrderStatus::COMPLETED) {

            $order->loadMissing(['store', 'items']);

            $order->items->each(
                function ($orderItem) use ($order) {
                    if ($orderItem->item_type == 'custom-item') {
                        return;
                    }

                    $attributes = [
                        'action_id' => $order->id,
                        'action_type' => $order->getMorphClass(),
                        'stockable_id' => $orderItem->item_id,
                        'stockable_type' => $orderItem->item_type,
                    ];

                    $values = [
                        'store_id' => $order->store?->id,
                        'type' => StockMovementType::SALE->value,
                        'quantity' => $orderItem->quantity * -1,
                    ];

                    StockMovement::query()->updateOrCreate($attributes, $values);
                }
            );
        }

        $order->loadMissing(['items.item']);

        $products = $order->items->map(fn ($i) => $i->item)->filter(fn ($i) => $i->getMorphClass() !== 'custom-item');

        $products->each(function ($product) {

            app(StockMovementService::class)->updateStock($product);
        });
    }

    public function updated(Order $order): void
    {
        StockMovement::query()
            ->where('action_id', $order->id)
            ->where('action_type', $order->getMorphClass())
            ->forceDelete();

        if ($order->order_status === OrderStatus::COMPLETED) {

            $order->loadMissing(['store', 'items']);

            $order->items->each(
                function ($orderItem) use ($order) {
                    if ($orderItem->item_type === 'custom-item') {
                        return;
                    }

                    $attributes = [
                        'action_id' => $order->id,
                        'action_type' => $order->getMorphClass(),
                        'stockable_id' => $orderItem->item_id,
                        'stockable_type' => $orderItem->item_type,
                    ];

                    $values = [
                        'author_user_id' => $order->author_user_id,
                        'store_id' => $order->store?->id,
                        'type' => StockMovementType::SALE->value,
                        'quantity' => $orderItem->quantity * -1,
                    ];

                    StockMovement::query()->updateOrCreate($attributes, $values);
                }
            );
        }

        $order->fresh()->loadMissing(['items.item']);

        $products = $order->items->map(fn ($i) => $i->item)->filter(fn ($i) => $i->getMorphClass() !== 'custom-item');

        $products->each(function ($product) {

            app(StockMovementService::class)->updateStock($product);
        });
    }

    public function deleted(Order $order): void
    {
        $order->loadMissing(['items.item']);

        $products = $order->items->map(fn ($i) => $i->item)->filter(fn ($i) => $i->getMorphClass() !== 'custom-item');

        StockMovement::query()
            ->where('action_id', $order->id)
            ->where('action_type', $order->getMorphClass())
            ->delete();

        $products->each(function ($product) {

            app(StockMovementService::class)->updateStock($product);
        });
    }

    public function restored(Order $order): void
    {

        StockMovement::query()
            ->where('action_id', $order->id)
            ->where('action_type', $order->getMorphClass())
            ->restore();

        $order->fresh()->loadMissing(['items.item']);

        $products = $order->items->map(fn ($i) => $i->item)->filter(fn ($i) => $i->getMorphClass() !== 'custom-item');

        $products->each(function ($product) {

            app(StockMovementService::class)->updateStock($product);
        });
    }

    public function forceDeleted(Order $order): void
    {

        StockMovement::query()
            ->where('action_id', $order->id)
            ->where('action_type', $order->getMorphClass())
            ->forceDelete();

        $order->fresh()->loadMissing(['items.item']);

        $products = $order->items->map(fn ($i) => $i->item)->filter(fn ($i) => $i->getMorphClass() !== 'custom-item');

        $products->each(function ($product) {

            app(StockMovementService::class)->updateStock($product);
        });
    }
}
