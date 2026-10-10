<?php

namespace Tests\Feature\EntityManagement;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\OrganizationEntityDemoSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrganizationDemoSeederTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $key): User
    {
        return User::where('email', 'demo.'.str_replace('_', '.', $key).'@'.OrganizationEntityDemoSeeder::EMAIL_DOMAIN)->firstOrFail();
    }

    private function interprete(string $key): Interprete
    {
        return Interprete::where('slug', OrganizationEntityDemoSeeder::SLUG_PREFIX.str_replace('_', '-', $key))->firstOrFail();
    }

    private function demoOrganizations()
    {
        return Organization::where('slug', 'like', OrganizationEntityDemoSeeder::SLUG_PREFIX.'%');
    }

    /** @test */
    public function demo_scenarios_grant_the_expected_permissions(): void
    {
        $this->seed(OrganizationEntityDemoSeeder::class);

        $festival = Festival::where('slug', OrganizationEntityDemoSeeder::SLUG_PREFIX.'festival-del-rio')->firstOrFail();

        // Solista
        $this->assertTrue($this->user('ana')->canManage($this->interprete('ana')));
        $this->assertFalse($this->user('ana')->canManage($this->interprete('duo')));

        // Dúo: responsable y ayudante sí; el solicitante pendiente no
        $this->assertTrue($this->user('duo_owner')->canManage($this->interprete('duo')));
        $this->assertTrue($this->user('duo_ayudante')->canManage($this->interprete('duo')));
        $this->assertFalse($this->user('duo_solicitante')->canManage($this->interprete('duo')));

        // Productora: administra sus 3 artistas y también la ficha del Dúo (segunda organización)
        foreach (['trio', 'cantor', 'banda', 'duo'] as $key) {
            $this->assertTrue($this->user('productora')->canManage($this->interprete($key)), "productora debería administrar $key");
        }
        $this->assertFalse($this->user('productora')->canManage($this->interprete('ana')));

        // Festival: editor sí, lector (member) no
        $this->assertTrue($this->user('festival_editor')->canManage($festival));
        $this->assertFalse($this->user('festival_lector')->canManage($festival));

        // Organización inactiva: permisos revocados
        $this->assertFalse($this->user('inactiva_editor')->canManage($this->interprete('inactiva')));

        // Acceso legacy sin organización y usuario sin vínculos
        $this->assertTrue($this->user('heredada')->canManage($this->interprete('heredada')));
        $this->assertFalse($this->user('heredada')->canManage($this->interprete('sin_org')));
        foreach (['ana', 'duo', 'trio', 'inactiva', 'heredada', 'sin_org'] as $key) {
            $this->assertFalse($this->user('sin_vinculo')->canManage($this->interprete($key)));
        }
    }

    /** @test */
    public function demo_contactable_and_primary_rules(): void
    {
        $this->seed(OrganizationEntityDemoSeeder::class);

        $contactable = $this->demoOrganizations()->contactable()->pluck('slug')->all();
        $prefix = OrganizationEntityDemoSeeder::SLUG_PREFIX;

        $this->assertEqualsCanonicalizing(
            [$prefix.'ana', $prefix.'duo', $prefix.'productora', $prefix.'festival', $prefix.'prensa-sin-cuenta'],
            $contactable,
            'Quedan afuera la inactiva y la que se dio de baja.'
        );

        $duo = $this->interprete('duo');
        $this->assertCount(2, $duo->organizations);
        $this->assertSame($prefix.'duo', $duo->primaryOrganization()->slug, 'La principal del Dúo es su propia organización.');
    }

    /** @test */
    public function seeder_is_idempotent_and_purge_removes_only_demo_data(): void
    {
        $realOrganization = Organization::create(['type' => 'artista', 'name' => 'Real '.uniqid(), 'status' => 'active']);

        $this->seed(OrganizationEntityDemoSeeder::class);
        $this->seed(OrganizationEntityDemoSeeder::class);

        $this->assertSame(7, $this->demoOrganizations()->count());
        $this->assertSame(8, Interprete::where('slug', 'like', OrganizationEntityDemoSeeder::SLUG_PREFIX.'%')->count());
        $this->assertSame(10, User::where('email', 'like', '%@'.OrganizationEntityDemoSeeder::EMAIL_DOMAIN)->count());

        OrganizationEntityDemoSeeder::purge();

        $this->assertSame(0, $this->demoOrganizations()->count());
        $this->assertSame(0, Interprete::where('slug', 'like', OrganizationEntityDemoSeeder::SLUG_PREFIX.'%')->count());
        $this->assertSame(0, Festival::where('slug', 'like', OrganizationEntityDemoSeeder::SLUG_PREFIX.'%')->count());
        $this->assertSame(0, User::where('email', 'like', '%@'.OrganizationEntityDemoSeeder::EMAIL_DOMAIN)->count());
        $this->assertDatabaseHas('organizations', ['id' => $realOrganization->id]);
    }
}
