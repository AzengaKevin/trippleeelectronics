<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Store extends Model
{
    use HasAuthor, HasFactory, HasUuids, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
        'short_name',
        'email',
        'phone',
        'paybill',
        'account_number',
        'till',
        'kra_pin',
        'address',
        'location',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->using(StoreUser::class)->withTimestamps();
    }

    public function paymentMethods(): BelongsToMany
    {
        return $this->belongsToMany(PaymentMethod::class)
            ->using(PaymentMethodStore::class)
            ->withPivot(
                'phone_number',
                'paybill_number',
                'account_number',
                'till_number',
                'account_name',
            )
            ->withTimestamps();
    }

    public function toSearchableArray()
    {
        return $this->only('name', 'address', 'location');
    }

    public static function getExportFilename(): string
    {
        return str('stores')
            ->append('-')
            ->append(now()->format('Y-m-d'))
            ->append('.xlsx')
            ->toString();
    }
}
