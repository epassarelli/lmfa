<?php

namespace App\Actions\Fortify;

<<<<<<< HEAD
use App\Models\User;
=======
<<<<<<< HEAD
=======
use App\Models\User;
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
<<<<<<< HEAD
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
=======
<<<<<<< HEAD
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
=======
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
<<<<<<< HEAD
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
=======
<<<<<<< HEAD
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
=======
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
