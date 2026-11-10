<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\{DB, File, Http, Queue};
use Illuminate\Support\Str;
use Tests\TestCase;

class SocialPublicationOperatorCommandTest extends TestCase
{
    use DatabaseTransactions;
    private string $originalStorage;
    private string $fixtureStorage;

    protected function setUp(): void
    {
        parent::setUp();
        if (!getenv('CI')) { $this->assertSame('mfa_test', DB::connection()->getDatabaseName()); }
        Http::fake(); Queue::fake();
        $this->originalStorage = storage_path();
        $this->fixtureStorage = sys_get_temp_dir().'/mfa-social-command-'.Str::uuid();
        $this->app->useStoragePath($this->fixtureStorage);
    }

    protected function tearDown(): void
    {
        $this->app->useStoragePath($this->originalStorage);
        // Only this test's generated directory under the system temp path is disposable.
        if (str_starts_with($this->fixtureStorage, sys_get_temp_dir().'/mfa-social-command-')) { File::deleteDirectory($this->fixtureStorage); }
        parent::tearDown();
    }

    private function operator(): User
    {
        $this->artisan('social-publications:provision-operator', ['--apply'=>true])->assertSuccessful();
        $user = User::where('email','mfa-social-automation@localhost.invalid')->firstOrFail();
        config(['social_publication.operators'=>[$user->id]]);
        return $user;
    }

    public function test_dry_run_creates_neither_user_nor_permissions(): void
    {
        $before=DB::table('permissions')->count();
        $this->artisan('social-publications:provision-operator')->assertSuccessful();
        $this->assertDatabaseMissing('users',['email'=>'mfa-social-automation@localhost.invalid']);
        $this->assertSame($before,DB::table('permissions')->count());
    }

    public function test_provision_is_idempotent_and_grants_only_two_permissions(): void
    {
        $user=$this->operator();
        $this->artisan('social-publications:provision-operator',['--apply'=>true])->assertSuccessful();
        $this->assertSame(1,User::where('email',$user->email)->count());
        $this->assertSame(0,$user->roles()->count()); $this->assertSame(2,$user->getAllPermissions()->count());
        $this->assertSame(0,$user->tokens()->count());
    }

    public function test_reserved_identity_with_another_purpose_is_not_modified(): void
    {
        $user=User::factory()->create(['email'=>'mfa-social-automation@localhost.invalid','name'=>'Otra finalidad de prueba']);
        $this->artisan('social-publications:provision-operator',['--apply'=>true])->assertFailed();
        $this->assertSame(0,$user->permissions()->count());
    }

    public function test_private_export_is_scoped_and_never_overwrites_existing_file(): void
    {
        $user=$this->operator();
        $this->artisan('social-publications:issue-token',['user'=>$user->id,'--issue'=>true,'--n8n-file'=>true])->assertSuccessful();
        $file=storage_path('app/private/social-publications-n8n.json');
        $this->assertFileExists($file);
        $credential=json_decode(file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
        $this->assertTrue($credential[0]['type']==='httpHeaderAuth');
        $this->assertTrue(str_starts_with($credential[0]['data']['value'],'Bearer '));
        $this->assertSame(['social-publications:read','social-publications:execute'],$user->tokens()->firstOrFail()->abilities);
        $before=hash_file('sha256',$file);
        $this->artisan('social-publications:issue-token',['user'=>$user->id,'--issue'=>true,'--n8n-file'=>true])->assertFailed();
        $this->assertSame($before,hash_file('sha256',$file)); $this->assertSame(1,$user->tokens()->count());
    }

    public function test_issue_without_explicit_issue_flag_does_not_emit_token(): void
    {
        $user=$this->operator();
        $this->artisan('social-publications:issue-token',['user'=>$user->id,'--n8n-file'=>true])->assertSuccessful();
        $this->assertSame(0,$user->tokens()->count()); $this->assertFileDoesNotExist(storage_path('app/private/social-publications-n8n.json'));
    }
}
