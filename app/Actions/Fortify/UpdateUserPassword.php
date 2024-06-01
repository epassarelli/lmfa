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
     * @param  array<string, string>  $input
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
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
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
