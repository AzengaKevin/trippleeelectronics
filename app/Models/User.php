<?php

namespace App\Models;

use App\Models\Concerns\HasAuthor;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use CausesActivity, HasAuthor, HasFactory, HasRoles, HasUuids, InteractsWithMedia, Notifiable, Searchable, SoftDeletes;

    protected $fillable = [
        'author_user_id',
        'name',
        'email',
        'phone',
        'password',
        'dob',
        'address',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'dob' => 'date',
        ];
    }

    public function toSearchableArray()
    {
        return $this->only([
            'id',
            'name',
            'email',
            'phone',
        ]);
    }

    public function failed($exception)
    {
        activity()
            ->causedBy($this)
            ->withProperties(['exception' => $exception])
            ->log($exception?->getMessage() ?? 'An error occurred during the operation.');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->using(StoreUser::class)->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function threads(): BelongsToMany
    {
        return $this->belongsToMany(Thread::class)
            ->using(ThreadUser::class)
            ->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class)
            ->using(GroupUser::class)
            ->withTimestamps();
    }

    public function messagesRead(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_read')
            ->using(MessageRead::class)
            ->withTimestamps();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public static function getExportFileName(): string
    {
        return str('users')->append('-', now()->format('Y-m-d'))->append('.xlsx')->value();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 128, 128)
            ->nonQueued();
    }
}
