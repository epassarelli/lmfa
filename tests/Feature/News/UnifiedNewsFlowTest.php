<?php

namespace Tests\Feature\News;

use App\Models\Categoria;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnifiedNewsFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $colaborador;
    protected $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::fake('local');

        Role::findOrCreate('administrador', 'web');
        Role::findOrCreate('colaborador', 'web');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('administrador');
        $this->colaborador = User::factory()->create();
        $this->colaborador->assignRole('colaborador');
        $this->categoria = Categoria::first() ?: Categoria::create(['nombre' => 'General', 'slug' => 'general']);
    }

    /** @test */
    public function it_creates_news_from_uploaded_file_in_backend()
    {
        $this->actingAs($this->admin);

        $file = UploadedFile::fake()->image('news.jpg');

        $response = $this->post(route('backend.news.store'), [
            'titulo' => 'Noticia de Prueba',
            'slug' => 'noticia-de-prueba',
            'noticia' => 'Contenido de la noticia',
            'categoria_id' => $this->categoria->id,
            'estado' => 1,
            'foto' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('news', ['title' => 'Noticia de Prueba']);
        
        $news = News::where('title', 'Noticia de Prueba')->first();
        $this->assertCount(1, $news->images);
    }
}
