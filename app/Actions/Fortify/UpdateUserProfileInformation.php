<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'dob' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', File::image()->max(1024 * 1)],
        ])->validateWithBag('updateProfileInformation');

        if (
            $input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail
        ) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => data_get($input, 'name', $user->name),
                'email' => data_get($input, 'email', $user->email),
                'phone' => data_get($input, 'phone', $user->phone),
                'dob' => data_get($input, 'dob', $user->dob),
                'address' => data_get($input, 'address', $user->address),
            ])->save();
        }

        if ($avatar = data_get($input, 'avatar')) {

            $user->clearMediaCollection('avatar');

            $user->addMedia($avatar)->preservingOriginal()->toMediaCollection('avatar');
        }
    }

    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => data_get($input, 'name', $user->name),
            'email' => data_get($input, 'email', $user->email),
            'phone' => data_get($input, 'phone', $user->phone),
            'dob' => data_get($input, 'dob', $user->dob),
            'address' => data_get($input, 'address', $user->address),
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
