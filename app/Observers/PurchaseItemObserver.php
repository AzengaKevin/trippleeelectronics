<?php

namespace App\Observers;

use App\Models\Enums\StockMovementType;
use App\Models\PurchaseItem;
use App\Models\StockMovement;

class PurchaseItemObserver
{
    public function created(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->load(['purchase.store']);

        $attributes = [
            'action_id' => $purchaseItem->purchase->id,
            'action_type' => $purchaseItem->purchase->getMorphClass(),
            'stockable_id' => $purchaseItem->item_id,
            'stockable_type' => $purchaseItem->item_type,
        ];

        $values = [
            'store_id' => $purchaseItem->purchase?->store?->id,
            'type' => StockMovementType::PURCHASE->value,
            'quantity' => $purchaseItem->quantity,
        ];

        StockMovement::query()->updateOrCreate($attributes, $values);
    }

    public function updated(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->load(['purchase.store']);

        $attributes = [
            'action_id' => $purchaseItem->purchase->id,
            'action_type' => $purchaseItem->purchase->getMorphClass(),
            'stockable_id' => $purchaseItem->item_id,
            'stockable_type' => $purchaseItem->item_type,
        ];

        $values = [
            'store_id' => $purchaseItem->purchase?->store?->id,
            'type' => StockMovementType::PURCHASE,
            'quantity' => $purchaseItem->quantity,
        ];

        StockMovement::query()->updateOrCreate($attributes, $values);
    }

    public function deleted(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->load(['purchase.store']);

        StockMovement::query()->where([
            'action_id', $purchaseItem->purchase->id,
            'action_type', $purchaseItem->purchase->getMorphClass(),
            'stockable_id', $purchaseItem->item_id,
            'stockable_type', $purchaseItem->item_type,
        ])->first()?->delete();
    }

    public function restored(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->load(['purchase.store']);

        StockMovement::query()->onlyTrashed()->where([
            'action_id', $purchaseItem->purchase->id,
            'action_type', $purchaseItem->purchase->getMorphClass(),
            'stockable_id', $purchaseItem->item_id,
            'stockable_type', $purchaseItem->item_type,
        ])->first()?->restore();
    }

    public function forceDeleted(PurchaseItem $purchaseItem): void
    {
        $purchaseItem->load(['purchase.store']);

        StockMovement::query()->withTrashed()->where([
            'action_id', $purchaseItem->purchase->id,
            'action_type', $purchaseItem->purchase->getMorphClass(),
            'stockable_id', $purchaseItem->item_id,
            'stockable_type', $purchaseItem->item_type,
        ])->first()?->forceDelete();
    }
}
