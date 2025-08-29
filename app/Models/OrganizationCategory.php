<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class OrganizationCategory extends Model
{
    use HasAuthor, HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
    ];

    public function toSearchableArray()
    {
        return $this->only('name');
    }

    public static function getExportFilename(): string
    {
        return str('organization-categories-')->append(now()->format('Y-m-d'))->append('.xlsx')->value();
    }
}
