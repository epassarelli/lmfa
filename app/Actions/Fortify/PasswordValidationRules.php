<?php

namespace App\Actions\Fortify;

<<<<<<< HEAD
use Illuminate\Validation\Rules\Password;
=======
<<<<<<< HEAD
use Laravel\Fortify\Rules\Password;
=======
use Illuminate\Validation\Rules\Password;
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
<<<<<<< HEAD
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
=======
<<<<<<< HEAD
     * @return array
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
     */
    protected function passwordRules(): array
    {
<<<<<<< HEAD
        return ['required', 'string', Password::default(), 'confirmed'];
=======
        return ['required', 'string', new Password, 'confirmed'];
=======
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    }
}
