<?php

namespace App\Models;

use App\Models\Enums\StockableType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockLevel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'store_id',
        'stockable_id',
        'stockable_type',
        'quantity',
    ];

    protected function casts()
    {
        return [
            'quantity' => 'integer',
            'stockable_type' => StockableType::class,
        ];
    }

    public function stockable()
    {
        return $this->morphTo();
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public static function getExportFilename(): string
    {
        return str('stock-levels-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx');
    }
}
