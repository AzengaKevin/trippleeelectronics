<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kalnoy\Nestedset\NodeTrait;

class Jurisdiction extends Model
{
    use HasFactory, HasUuids, NodeTrait, SoftDeletes;

    protected $fillable = ['name'];

    public function toSearchableArray()
    {
        return $this->only('name');
    }
}
