<?php

namespace App\Actions\Fortify;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
<<<<<<< HEAD
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
=======
<<<<<<< HEAD
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
=======
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]), function (User $user) {
                $this->createTeam($user);
            });
        });
    }

    /**
     * Create a personal team for the user.
<<<<<<< HEAD
     */
    protected function createTeam(User $user): void
=======
<<<<<<< HEAD
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user)
=======
     */
    protected function createTeam(User $user): void
>>>>>>> dev
>>>>>>> f6a1528eb1fc36098adb0d1c90a2861b3666f2d7
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}
