<?php

namespace App\Models;

use App\Models\Enums\ContractStatus;
use App\Models\Enums\ContractType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contract extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'contract_type',
        'start_date',
        'end_date',
        'salary',
        'status',
        'responsibilities',
    ];

    protected function casts()
    {
        return [
            'contract_type' => ContractType::class,
            'status' => ContractStatus::class,
            'responsibilities' => 'array',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
