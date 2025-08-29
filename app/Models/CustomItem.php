<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class CustomItem extends Model
{
    use HasAuthor, HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
        'pos_name',
        'description',
        'pos_description',
        'cost',
        'price',
    ];
}
