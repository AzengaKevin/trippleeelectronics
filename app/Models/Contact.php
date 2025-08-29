<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Contact extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
    ];

    public function toSearchableArray()
    {
        return $this->only('name', 'email', 'phone', 'message');
    }
}
