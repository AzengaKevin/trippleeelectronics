<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Enums\StockableType;
use App\Models\Enums\StockMovementActionType;
use App\Models\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'store_id',
        'stockable_id',
        'stockable_type',
        'related_stock_movement_id',
        'action_id',
        'action_type',
        'type',
        'quantity',
        'description',
        'cost_implication',
    ];

    protected function casts()
    {
        return [
            'type' => StockMovementType::class,
            'cost_implication' => 'decimal:2',
            'stockable_type' => StockableType::class,
            'action_type' => StockMovementActionType::class,
        ];
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stockable()
    {
        return $this->morphTo();
    }

    public function robustStockable()
    {
        return $this->morphTo(__FUNCTION__, 'stockable_type', 'stockable_id')->withTrashed();
    }

    public function action()
    {
        return $this->morphTo()->withTrashed();
    }

    public function scopeType($query, StockMovementType $type)
    {
        return $query->where('type', $type);
    }

    public function relatedStockMovement()
    {
        return $this->belongsTo(StockMovement::class, 'related_stock_movement_id');
    }

    public static function getExportFilename(): string
    {
        return str('stock-movements')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx');
    }
}
