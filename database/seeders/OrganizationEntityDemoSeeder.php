<?php

namespace Database\Seeders;

use App\Models\EntityClaimRequest;
use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\Organization;
use App\Models\OrganizationEntity;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Datos DEMO para probar el circuito organizaciones <-> fichas <-> miembros en desarrollo.
 *
 * - Solo entorno local/dev: se niega a correr en producción.
 * - Idempotente: se puede ejecutar varias veces sin duplicar.
 * - Reversible: OrganizationEntityDemoSeeder::purge() elimina solo lo que crea este seeder.
 * - Todo lleva el prefijo "[DEMO LOCAL]", slugs "demo-org-*" o emails @demo-local.example.test.
 * - Las fichas demo quedan sin publicar (intérpretes estado 0, festival draft).
 * - Los datos son ficticios: no acreditan calidad editorial ni sirven para publicar.
 */
class OrganizationEntityDemoSeeder extends Seeder
{
    public const PREFIX = '[DEMO LOCAL]';
    public const SLUG_PREFIX = 'demo-org-';
    public const EMAIL_DOMAIN = 'demo-local.example.test';

    // Credencial de prueba SOLO para cuentas demo locales (el seeder no corre en producción).
    private const DEMO_PASSWORD = 'demo-local-2026';

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('Este seeder no corre en producción.');

            return;
        }

        $users = $this->users();
        $fichas = $this->fichas($users);
        $this->organizations($users, $fichas);
        $this->claims($users, $fichas);

        $this->command?->info('Datos demo de organizaciones listos ('.count($users).' usuarios, '.count($fichas).' fichas).');
    }

    /**
     * Elimina solo los datos creados por este seeder.
     */
    public static function purge(): void
    {
        $demoUserIds = User::query()->where('email', 'like', '%@'.self::EMAIL_DOMAIN)->pluck('id');
        EntityClaimRequest::query()->whereIn('user_id', $demoUserIds)->delete();
        Organization::query()->where('slug', 'like', self::SLUG_PREFIX.'%')->get()->each->delete();
        Interprete::query()->where('slug', 'like', self::SLUG_PREFIX.'%')->delete();
        Festival::query()->where('slug', 'like', self::SLUG_PREFIX.'%')->delete();
        User::query()->where('email', 'like', '%@'.self::EMAIL_DOMAIN)->get()->each->delete();
    }

    /**
     * @return array<string, User>
     */
    private function users(): array
    {
        $definitions = [
            'ana' => ['Ana Solista', 'colaborador'],
            'duo_owner' => ['Dúo Responsable', 'colaborador'],
            'duo_ayudante' => ['Dúo Ayudante', 'colaborador'],
            'duo_solicitante' => ['Dúo Solicitante', 'colaborador'],
            'productora' => ['Productora Cuerdas', 'prensa'],
            'festival_editor' => ['Festival Editor', 'colaborador'],
            'festival_lector' => ['Festival Lector', 'colaborador'],
            'inactiva_editor' => ['Agrupación Inactiva Editor', 'colaborador'],
            'heredada' => ['Creador de Ficha Heredada', 'colaborador'],
            'sin_vinculo' => ['Colaborador Sin Vínculo', 'colaborador'],
        ];

        $users = [];

        foreach ($definitions as $key => [$name, $role]) {
            Role::findOrCreate($role, 'web');

            $user = User::query()->firstOrNew(['email' => 'demo.'.str_replace('_', '.', $key).'@'.self::EMAIL_DOMAIN]);
            $user->forceFill([
                'name' => self::PREFIX.' '.$name,
                'password' => $user->exists ? $user->password : Hash::make(self::DEMO_PASSWORD),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
            $user->syncRoles([$role]);

            $users[$key] = $user;
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     * @return array<string, Interprete|Festival>
     */
    private function fichas(array $users): array
    {
        $month = Mes::first() ?: Mes::create(['nombre' => 'Enero']);

        $interpretes = [
            'ana' => 'Ana Solista',
            'duo' => 'Dúo Los Demo',
            'trio' => 'Trío Uno',
            'cantor' => 'Cantor Dos',
            'banda' => 'Banda Tres',
            'inactiva' => 'Agrupación Inactiva',
            'heredada' => 'Ficha Heredada',
            'sin_org' => 'Ficha Sin Organización',
        ];

        $fichas = [];

        foreach ($interpretes as $key => $name) {
            $ficha = Interprete::query()->firstOrNew(['slug' => self::SLUG_PREFIX.str_replace('_', '-', $key)]);
            $ficha->forceFill([
                'interprete' => self::PREFIX.' '.$name,
                'biografia' => 'Ficha de demostración para probar permisos por organización. Contenido ficticio.',
                'estado' => 0,
                'user_id' => $key === 'heredada' ? $users['heredada']->id : 1,
            ])->save();

            $fichas['interprete_'.$key] = $ficha;
        }

        $festival = Festival::query()->firstOrNew(['slug' => self::SLUG_PREFIX.'festival-del-rio']);
        $festival->forceFill([
            'title' => self::PREFIX.' Festival Demo del Río',
            'mes_id' => $month->id,
            'status' => 'draft',
            'user_id' => 1,
        ])->save();
        $fichas['festival'] = $festival;

        return $fichas;
    }

    /**
     * Solicitudes pendientes para probar la revisión del administrador.
     *
     * @param  array<string, User>  $users
     * @param  array<string, Interprete|Festival>  $fichas
     */
    private function claims(array $users, array $fichas): void
    {
        $requests = [
            // Ficha sin organización: al aprobar se crea una y el usuario queda como responsable.
            ['sin_vinculo', 'interprete_sin_org', 'artist', 'Soy integrante del grupo. Pueden confirmarlo por el Instagram oficial de la agrupación.'],
            // Ficha con organización y responsable activo: se sugiere rol editor.
            ['duo_solicitante', 'interprete_cantor', 'representative', 'Represento al cantor para sus presentaciones. Mi contacto de producción está en su sitio web.'],
            // Festival con organización existente.
            ['sin_vinculo', 'festival', 'organizer', 'Integro la comisión organizadora del festival y quiero cargar la grilla.'],
        ];

        foreach ($requests as [$userKey, $fichaKey, $relationship, $message]) {
            $ficha = $fichas[$fichaKey];

            EntityClaimRequest::query()->firstOrCreate(
                [
                    'user_id' => $users[$userKey]->id,
                    'entity_type' => $ficha->getMorphClass(),
                    'entity_id' => $ficha->getKey(),
                    'status' => EntityClaimRequest::PENDING,
                ],
                ['relationship' => $relationship, 'message' => $message]
            );
        }
    }

    /**
     * @param  array<string, User>  $users
     * @param  array<string, Interprete|Festival>  $fichas
     */
    private function organizations(array $users, array $fichas): void
    {
        $organizations = [
            'ana' => [
                'attributes' => ['type' => 'artista', 'name' => 'Ana Solista', 'email' => 'ana@'.self::EMAIL_DOMAIN, 'source' => 'claim', 'verified' => true, 'consent' => true, 'instagram' => '@ana.solista.demo', 'stage' => 'email_obtained'],
                'links' => [['interprete_ana', true]],
                'members' => [['ana', 'owner', 'active']],
            ],
            'duo' => [
                'attributes' => ['type' => 'artista', 'name' => 'Dúo Los Demo', 'email' => 'duo@'.self::EMAIL_DOMAIN, 'source' => 'email_reply', 'verified' => true, 'consent' => true, 'instagram' => '@duo.los.demo', 'stage' => 'email_obtained'],
                'links' => [['interprete_duo', true]],
                'members' => [
                    ['duo_owner', 'owner', 'active'],
                    ['duo_ayudante', 'editor', 'active'],
                    ['duo_solicitante', 'editor', 'pending'],
                ],
            ],
            'productora' => [
                'attributes' => ['type' => 'productora', 'name' => 'Productora Cuerdas', 'email' => 'productora@'.self::EMAIL_DOMAIN, 'source' => 'manual', 'verified' => false, 'consent' => true, 'stage' => 'email_obtained'],
                // Además vincula (sin ser principal) la ficha del Dúo: una ficha con dos organizaciones.
                'links' => [['interprete_trio', true], ['interprete_cantor', true], ['interprete_banda', true], ['interprete_duo', false]],
                'members' => [['productora', 'owner', 'active']],
            ],
            'festival' => [
                'attributes' => ['type' => 'festival', 'name' => 'Comisión Festival del Río', 'email' => 'festival@'.self::EMAIL_DOMAIN, 'source' => 'email_reply', 'verified' => true, 'consent' => true],
                'links' => [['festival', true]],
                'members' => [
                    ['festival_editor', 'editor', 'active'],
                    ['festival_lector', 'member', 'active'],
                ],
            ],
            'inactiva' => [
                'attributes' => ['type' => 'artista', 'name' => 'Agrupación Inactiva', 'email' => 'inactiva@'.self::EMAIL_DOMAIN, 'source' => 'manual', 'status' => 'inactive', 'verified' => false, 'consent' => false],
                'links' => [['interprete_inactiva', true]],
                'members' => [['inactiva_editor', 'editor', 'active']],
            ],
            // Contactos sin cuenta ni fichas: respondieron por mail o se dieron de baja.
            'prensa-sin-cuenta' => [
                'attributes' => ['type' => 'prensa', 'name' => 'Prensa Sin Cuenta', 'email' => 'prensa@'.self::EMAIL_DOMAIN, 'source' => 'email_reply', 'verified' => false, 'consent' => true, 'stage' => 'email_obtained'],
                'links' => [],
                'members' => [],
            ],
            'pena-con-baja' => [
                'attributes' => ['type' => 'peña', 'name' => 'Peña Con Baja', 'email' => 'pena@'.self::EMAIL_DOMAIN, 'source' => 'import', 'verified' => false, 'consent' => true, 'unsubscribed' => true, 'stage' => 'declined'],
                'links' => [],
                'members' => [],
            ],
            // Contacto en curso por redes: todavía no hay email, así que no son contactables.
            'prospecto-instagram' => [
                'attributes' => ['type' => 'artista', 'name' => 'Prospecto Por Instagram', 'email' => null, 'source' => 'manual', 'verified' => false, 'consent' => false, 'instagram' => '@prospecto.demo', 'stage' => 'prospect'],
                'links' => [],
                'members' => [],
            ],
            'contactado-facebook' => [
                'attributes' => ['type' => 'festival', 'name' => 'Festival Contactado Por Facebook', 'email' => null, 'source' => 'manual', 'verified' => false, 'consent' => false, 'facebook' => 'festival.demo.facebook', 'stage' => 'contacted', 'contacted_days_ago' => 3],
                'links' => [],
                'members' => [],
            ],
        ];

        foreach ($organizations as $key => $definition) {
            $a = $definition['attributes'];

            $organization = Organization::query()->firstOrNew(['slug' => self::SLUG_PREFIX.$key]);
            $organization->forceFill([
                'type' => $a['type'],
                'name' => self::PREFIX.' '.$a['name'],
                'email' => $a['email'] ?? null,
                'instagram' => $a['instagram'] ?? null,
                'facebook' => $a['facebook'] ?? null,
                'contact_stage' => $a['stage'] ?? 'prospect',
                'last_contacted_at' => isset($a['contacted_days_ago'])
                    ? ($organization->last_contacted_at ?? now()->subDays($a['contacted_days_ago']))
                    : $organization->last_contacted_at,
                'source' => $a['source'],
                'status' => $a['status'] ?? 'active',
                'is_verified' => $a['verified'],
                'verified_at' => $a['verified'] ? ($organization->verified_at ?? now()) : null,
                'verification_method' => $a['verified'] ? 'manual' : null,
                'contact_consent_at' => $a['consent'] ? ($organization->contact_consent_at ?? now()) : null,
                'contact_consent_purpose' => $a['consent'] ? 'demo: novedades y convocatorias' : null,
                'unsubscribed_at' => ($a['unsubscribed'] ?? false) ? ($organization->unsubscribed_at ?? now()) : null,
            ])->save();

            foreach ($definition['links'] as [$fichaKey, $primary]) {
                $ficha = $fichas[$fichaKey];

                OrganizationEntity::query()->updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'entity_type' => $ficha->getMorphClass(),
                        'entity_id' => $ficha->getKey(),
                    ],
                    ['is_primary' => $primary]
                );
            }

            foreach ($definition['members'] as [$userKey, $role, $status]) {
                OrganizationMember::query()->updateOrCreate(
                    ['organization_id' => $organization->id, 'user_id' => $users[$userKey]->id],
                    ['role' => $role, 'status' => $status]
                );
            }
        }
    }
}
