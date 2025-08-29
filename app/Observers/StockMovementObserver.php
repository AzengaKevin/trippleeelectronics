<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Services\StockMovementService;

class StockMovementObserver
{
    public function created(StockMovement $stockMovement): void
    {

        app(StockMovementService::class)->updateStock($stockMovement->stockable);
    }

    public function updated(StockMovement $stockMovement): void
    {

        app(StockMovementService::class)->updateStock($stockMovement->stockable);
    }

    public function deleted(StockMovement $stockMovement): void
    {
        app(StockMovementService::class)->updateStock($stockMovement->stockable);
    }

    public function restored(StockMovement $stockMovement): void
    {
        app(StockMovementService::class)->updateStock($stockMovement->stockable);
    }

    public function forceDeleted(StockMovement $stockMovement): void
    {
        app(StockMovementService::class)->updateStock($stockMovement->stockable);
    }
}
