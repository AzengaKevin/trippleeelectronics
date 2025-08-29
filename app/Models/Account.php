<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use App\Models\Enums\AccountType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasAuthor, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
        'type',
    ];

    protected function casts()
    {
        return [
            'type' => AccountType::class,
        ];
    }
}
