<?php

namespace Database\Seeders;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\User;
use App\Services\OrganizationProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Cuentas y fichas DEMO para validar a mano el acceso por fichas en un entorno LOCAL.
 *
 *  - val.sinficha@example.test   colaborador sin fichas
 *  - val.artista@example.test    colaborador, responsable de un artista demo (sin publicar)
 *  - val.festival@example.test   colaborador, responsable de un festival demo (borrador)
 *  - val.verificado@example.test colaborador + rol verificado, sin fichas
 *
 * Idempotente. Las contraseñas son aleatorias y no se muestran: fijá la tuya con tinker (ver el
 * mensaje final). No corre fuera de local/testing. Datos demo: no acreditan contenido editorial.
 */
class AccessValidationAccountsSeeder extends Seeder
{
    public const ACCOUNTS = [
        'sinficha' => 'val.sinficha@example.test',
        'artista' => 'val.artista@example.test',
        'festival' => 'val.festival@example.test',
        'verificado' => 'val.verificado@example.test',
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('AccessValidationAccountsSeeder solo corre en entornos local/testing.');

            return;
        }

        Role::findOrCreate('colaborador', 'web');
        Role::findOrCreate('verificado', 'web');

        $users = [];
        foreach (self::ACCOUNTS as $key => $email) {
            $users[$key] = $this->account($key, $email);
        }

        $users['verificado']->assignRole('verificado');

        $ownerId = User::role('administrador')->value('id') ?? $users['artista']->id;
        $provisioner = app(OrganizationProvisioner::class);

        $artist = Interprete::query()->firstOrCreate(
            ['slug' => 'demo-validacion-artista'],
            ['interprete' => '[DEMO VALIDACIÓN] Artista', 'biografia' => '', 'estado' => 0, 'user_id' => $ownerId]
        );
        $provisioner->forEntity($artist, $users['artista']);

        $festival = Festival::query()->where('slug', 'demo-validacion-festival')->first();

        if (! $festival) {
            $festival = new Festival(['title' => '[DEMO VALIDACIÓN] Festival', 'status' => 'draft']);
            $festival->slug = 'demo-validacion-festival';
            $festival->mes_id = (Mes::query()->value('id') ?? Mes::create(['nombre' => 'Enero'])->id);
            $festival->user_id = $ownerId;
            $festival->save();
        }
        $provisioner->forEntity($festival, $users['festival']);

        $this->command?->info('Cuentas de validación listas (contraseñas aleatorias, no mostradas):');
        foreach (self::ACCOUNTS as $email) {
            $this->command?->line("  - $email");
        }
        $this->command?->line('Fijá una contraseña propia con: php artisan tinker  →  User::where(\'email\', \'val.sinficha@example.test\')->first()->update([\'password\' => Hash::make(\'TU_CLAVE\')]);');
    }

    private function account(string $key, string $email): User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $user = new User();
            $user->forceFill([
                'name' => 'Validación '.$key,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
            ])->save();
        }

        $user->assignRole('colaborador');

        return $user;
    }
}
