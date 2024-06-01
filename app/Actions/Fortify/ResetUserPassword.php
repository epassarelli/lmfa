<?php

namespace App\Actions\Fortify;

<<<<<<< HEAD
=======
use App\Models\User;
>>>>>>> dev
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
<<<<<<< HEAD
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function reset($user, array $input)
=======
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
>>>>>>> dev
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
