<?php

namespace Tests\Feature;

use App\Models\Festival;
use App\Models\Interprete;
use App\Models\Mes;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImageUploadCapTest extends TestCase
{
    use DatabaseTransactions;

    private function festival(): Festival
    {
        return Festival::create([
            'title' => 'Festival imagen '.uniqid(), 'slug' => 'f-img-'.uniqid(), 'status' => 'draft',
            'mes_id' => (Mes::first() ?: Mes::create(['nombre' => 'Enero']))->id,
        ]);
    }

    private function storedWidth(string $path): int
    {
        return getimagesizefromstring(Storage::disk('public')->get($path))[0];
    }

    public function test_the_stored_original_is_capped_to_the_widest_variant_of_the_profile(): void
    {
        Storage::fake('public');

        $image = app(ImageUploadService::class)->process(UploadedFile::fake()->image('grande.jpg', 2400, 1350), $this->festival(), 'festival', 'festivales');

        $this->assertSame(1600, $this->storedWidth($image->path));
        $this->assertSame(1600, (int) $image->original_width);
        $this->assertSame(900, (int) $image->original_height);
        $this->assertNotEmpty($image->variants_json['hero']);
    }

    public function test_a_smaller_image_is_kept_as_is_and_never_enlarged(): void
    {
        Storage::fake('public');

        $image = app(ImageUploadService::class)->process(UploadedFile::fake()->image('chica.jpg', 1000, 563), $this->festival(), 'festival', 'festivales');

        $this->assertSame(1000, $this->storedWidth($image->path));
        $this->assertSame(1000, (int) $image->original_width);
    }

    public function test_each_profile_uses_its_own_cap(): void
    {
        Storage::fake('public');
        $service = app(ImageUploadService::class);

        $artist = Interprete::create(['interprete' => 'Artista imagen '.uniqid(), 'slug' => 'a-img-'.uniqid(), 'biografia' => '<p>Bio</p>', 'estado' => 0]);

        $portrait = $service->process(UploadedFile::fake()->image('retrato.jpg', 1600, 2133), $artist, 'artist', 'interpretes');

        $this->assertSame(800, $this->storedWidth($portrait->path));
    }

    public function test_forms_tell_the_browser_the_exact_adjustment_to_apply(): void
    {
        $admin = User::factory()->create();
        Role::findOrCreate('administrador', 'web');
        $admin->assignRole('administrador');

        $expected = [
            'backend.festivales.create' => ['data-out-w="1600"', 'data-out-h="900"', 'data-ratio="16:9"'],
            'backend.interpretes.create' => ['data-out-w="800"', 'data-out-h="1067"', 'data-ratio="3:4"'],
            'backend.discos.create' => ['data-out-w="800"', 'data-out-h="800"', 'data-ratio="1:1"'],
            'backend.mitos.create' => ['data-out-w="900"', 'data-out-h="675"', 'data-ratio="4:3"'],
            'backend.classifieds.create' => ['data-out-w="1600"', 'data-out-h="900"'],
        ];

        foreach ($expected as $route => $attributes) {
            $page = $this->actingAs($admin)->get(route($route))->assertOk()->assertSee('image-editor-modal', false)->assertSee('cropperjs', false);

            foreach ($attributes as $attribute) {
                $page->assertSee($attribute, false);
            }
        }
    }
}
