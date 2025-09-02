<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Property extends Model
{
    use HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'active',
    ];

    public function toSearchableArray()
    {
        return $this->only(['name', 'code', 'address']);
    }

    public static function getExportFilename(): string
    {
        return str('properties-')->append(now()->format('Y-m-d'))->append('.xlsx')->value();
    }
}
