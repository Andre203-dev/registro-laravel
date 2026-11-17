<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Sin sesión, la página principal manda al login.
     */
    public function test_la_raiz_sin_sesion_redirige_al_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }
}
