<?php

namespace Tests\Feature;

use App\Support\ResponseCache\PublicPagesCacheProfile;
use Illuminate\Http\Request;
use Tests\TestCase;

class SocialLoginResponseCacheTest extends TestCase
{
    /** @dataProvider providers */
    public function test_each_oauth_attempt_has_its_own_state_with_response_cache_enabled(string $provider): void
    {
        config([
            'responsecache.enabled' => true,
            'responsecache.cache_store' => 'array',
            "services.{$provider}.client_id" => 'example-client',
            "services.{$provider}.client_secret" => 'example-secret',
            "services.{$provider}.redirect" => "http://localhost/auth/{$provider}/callback",
        ]);

        $states = [];
        for ($attempt = 0; $attempt < 2; $attempt++) {
            $response = $this->get("/auth/{$provider}")->assertRedirect();
            $response->assertHeaderMissing('X-ResponseCache-Cached-On');
            parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
            $this->assertNotEmpty($query['state'] ?? null);
            $this->assertSame(session('state'), $query['state']);
            $states[] = $query['state'];
        }

        $this->assertNotSame($states[0], $states[1]);
    }

    public static function providers(): array
    {
        return [['google'], ['facebook']];
    }

    /** @dataProvider oauthPaths */
    public function test_social_oauth_responses_are_never_cached(string $path): void
    {
        $this->assertFalse(app(PublicPagesCacheProfile::class)->shouldCacheRequest(Request::create($path)));
    }

    public static function oauthPaths(): array
    {
        return [
            ['/auth/google'],
            ['/auth/google/callback?code=example&state=example'],
            ['/auth/facebook'],
            ['/auth/facebook/callback?code=example&state=example'],
        ];
    }

    public function test_public_pages_remain_eligible_for_cache(): void
    {
        $this->assertTrue(app(PublicPagesCacheProfile::class)->shouldCacheRequest(Request::create('/')));
    }
}
