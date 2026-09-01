<?php

namespace Tests\Feature\Myths;

use App\Models\Mito;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MythBackofficeModernizationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function admin_can_keep_myth_inactive_without_changing_its_author(): void
    {
        Role::findOrCreate('administrador', 'web');

        $author = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('administrador');
        $this->actingAs($admin);

        $myth = Mito::create([
            'titulo' => 'Relato en revisión',
            'slug' => 'relato-en-revision',
            'mito' => '<p>Contenido inicial.</p>',
            'user_id' => $author->id,
            'visitas' => 0,
            'estado' => 1,
        ]);

        $response = $this->put(route('backend.mitos.update', $myth), [
            'titulo' => 'Relato en revisión',
            'slug' => 'relato-en-revision',
            'mito' => '<p>Contenido actualizado.</p>',
            'estado' => 0,
        ]);

        $response->assertRedirect(route('backend.mitos.index'));

        $myth->refresh();
        $this->assertSame(0, $myth->estado);
        $this->assertSame($author->id, $myth->user_id);
    }
}
