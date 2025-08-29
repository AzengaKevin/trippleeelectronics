<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suspension extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'employee_id',
        'from',
        'to',
        'reason',
    ];

    protected function casts()
    {
        return [
            'from' => 'date:Y-m-d',
            'to' => 'date:Y-m-d',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
