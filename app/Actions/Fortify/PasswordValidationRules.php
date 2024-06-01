<?php

namespace App\Actions\Fortify;

<<<<<<< HEAD
use Laravel\Fortify\Rules\Password;
=======
use Illuminate\Validation\Rules\Password;
>>>>>>> dev

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
<<<<<<< HEAD
     * @return array
     */
    protected function passwordRules()
    {
        return ['required', 'string', new Password, 'confirmed'];
=======
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', Password::default(), 'confirmed'];
>>>>>>> dev
    }
}
