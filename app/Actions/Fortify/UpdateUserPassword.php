<?php

namespace App\Actions\Fortify;

<<<<<<< HEAD
=======
use App\Models\User;
>>>>>>> dev
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

class UpdateUserPassword implements UpdatesUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and update the user's password.
     *
<<<<<<< HEAD
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
=======
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
>>>>>>> dev
    {
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
