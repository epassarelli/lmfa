<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private ?int $baseUserId = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Que una variable de entorno no cambie el comportamiento de toda la suite: los tests que
        // prueban el acceso por fichas lo encienden explícitamente.
        config(['backoffice_access.enabled' => false]);
    }

    /**
     * Usuario "dueño" para fixtures que necesitan un user_id válido sin asumir que existe el id 1
     * (en una base limpia, como la del CI, no existe).
     */
    protected function baseUserId(): int
    {
        return $this->baseUserId ??= \App\Models\User::factory()->create()->id;
    }
}
