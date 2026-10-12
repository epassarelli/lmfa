<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\OrganizationMember;
use App\Models\User;
use Database\Seeders\AccessValidationAccountsSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessValidationAccountsSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('verificado', 'web')->givePermissionTo(Permission::findOrCreate('publish contents', 'web'));
    }

    public function test_it_creates_the_four_accounts_and_two_unpublished_demo_fichas(): void
    {
        $this->seed(AccessValidationAccountsSeeder::class);

        $users = User::whereIn('email', array_values(AccessValidationAccountsSeeder::ACCOUNTS))->get()->keyBy('email');
        $this->assertCount(4, $users);
        $this->assertTrue($users['val.verificado@example.test']->hasRole('verificado'));
        $this->assertFalse($users['val.sinficha@example.test']->hasRole('verificado'));

        $artist = Interprete::where('slug', 'demo-validacion-artista')->firstOrFail();
        $festival = Festival::where('slug', 'demo-validacion-festival')->firstOrFail();
        $this->assertSame(0, (int) $artist->estado);
        $this->assertSame('draft', $festival->status);

        $this->assertTrue($users['val.artista@example.test']->canManage($artist));
        $this->assertTrue($users['val.festival@example.test']->canManage($festival));
        $this->assertFalse($users['val.sinficha@example.test']->canManage($artist));
        $this->assertSame(2, OrganizationMember::whereIn('user_id', $users->pluck('id'))->where('role', 'owner')->count());
    }

    public function test_passwords_are_random_hashed_and_it_is_idempotent(): void
    {
        $this->seed(AccessValidationAccountsSeeder::class);
        $first = User::where('email', 'val.sinficha@example.test')->firstOrFail();
        $hash = $first->password;

        $this->seed(AccessValidationAccountsSeeder::class);

        $this->assertSame(4, User::whereIn('email', array_values(AccessValidationAccountsSeeder::ACCOUNTS))->count());
        $this->assertSame(1, Interprete::where('slug', 'demo-validacion-artista')->count());
        $this->assertSame($hash, $first->fresh()->password, 'No debe cambiar una contraseña ya fijada.');
        $this->assertStringStartsWith('$2y$', $hash);
    }

    public function test_it_refuses_to_run_outside_local_environments(): void
    {
        $emails = array_values(AccessValidationAccountsSeeder::ACCOUNTS);
        User::whereIn('email', $emails)->get()->each->delete();
        $this->app->detectEnvironment(fn () => 'production');

        (new AccessValidationAccountsSeeder())->run();

        $this->assertSame(0, User::whereIn('email', $emails)->count());
    }
}
