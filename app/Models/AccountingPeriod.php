<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountingPeriod extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
        ];
    }

    public function name(): string
    {
        return str($this->start_date->format('M, Y'))->value();
    }
}
