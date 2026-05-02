<?php

namespace Tests\Feature\News;

use App\Models\Categoria;
use App\Models\Contribution;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

        $this->admin = User::factory()->create(['role' => 'administrador']);
        $this->colaborador = User::factory()->create(['role' => 'colaborador']);
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

    /** @test */
    public function it_approves_contribution_and_downloads_external_image()
    {
        $this->actingAs($this->admin);

        // Mock de la imagen externa
        Http::fake([
            'https://externo.com/foto.jpg' => Http::response(file_get_contents(public_path('img/logo-share.jpg')), 200, [
                'Content-Type' => 'image/jpeg'
            ]),
        ]);

        $contribution = Contribution::create([
            'user_id' => $this->colaborador->id,
            'contributable_type' => News::class,
            'status' => 'pending',
            'payload' => [
                'titulo' => 'Noticia Colaborada',
                'noticia' => 'Contenido colaborado',
                'categoria_id' => $this->categoria->id,
                'foto' => 'https://externo.com/foto.jpg',
            ]
        ]);

        $response = $this->get(route('backend.contributions.admin.approve', $contribution->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('news', [
            'title' => 'Noticia Colaborada',
            'created_by' => $this->colaborador->id,
            'approved_by' => $this->admin->id,
        ]);

        $news = News::where('title', 'Noticia Colaborada')->first();
        $this->assertCount(1, $news->images);
        $this->assertEquals('published', $news->editorial_status);
    }

    /** @test */
    public function it_blocks_ssrf_attempts_in_image_resolution()
    {
        $this->actingAs($this->admin);

        $contribution = Contribution::create([
            'user_id' => $this->colaborador->id,
            'contributable_type' => News::class,
            'status' => 'pending',
            'payload' => [
                'titulo' => 'Noticia Maliciosa',
                'noticia' => 'Contenido malicioso',
                'categoria_id' => $this->categoria->id,
                'foto' => 'http://127.0.0.1/admin/config', // Intento de SSRF
            ]
        ]);

        // La aplicación debería manejar la excepción y redirigir con error
        $response = $this->get(route('backend.contributions.admin.approve', $contribution->id));
        
        // El controlador actual no tiene un catch específico para ImageSecurityException todavía, 
        // pero el test fallará si el resolver no lanza la excepción.
        // Para este test, verificamos que NO se cree la noticia.
        $this->assertDatabaseMissing('news', ['title' => 'Noticia Maliciosa']);
    }
}
