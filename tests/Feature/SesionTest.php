<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DocenteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SesionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_correcto_entra_al_listado(): void
    {
        User::factory()->create(['email' => 'docente@example.com', 'password' => 'clave-ficticia']);

        $this->post('/login', ['email' => 'docente@example.com', 'password' => 'clave-ficticia'])
            ->assertRedirect('/estudiantes');

        $this->assertAuthenticated();
    }

    public function test_login_con_clave_incorrecta_se_rechaza(): void
    {
        User::factory()->create(['email' => 'docente@example.com', 'password' => 'clave-ficticia']);

        $this->from('/login')
            ->post('/login', ['email' => 'docente@example.com', 'password' => 'otra'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /** Caso 10 (segunda parte): después de salir se vuelve a exigir acceso */
    public function test_tras_logout_el_listado_vuelve_a_pedir_acceso(): void
    {
        $docente = User::factory()->create();

        $this->actingAs($docente)->get('/estudiantes')->assertOk();
        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
        $this->get('/estudiantes')->assertRedirect('/login');
    }

    public function test_seeder_crea_el_operador_y_no_reemplaza_una_clave_existente(): void
    {
        config(['registro.docente.email' => 'docente@example.com', 'registro.docente.clave' => 'primera-clave']);
        $this->seed(DocenteSeeder::class);

        $docente = User::where('email', 'docente@example.com')->first();
        $this->assertNotNull($docente);
        $this->assertNotSame('primera-clave', $docente->password);        // guardada como hash
        $this->assertTrue(Hash::check('primera-clave', $docente->password));

        // Segunda ejecución con otra clave: firstOrCreate no la cambia.
        config(['registro.docente.clave' => 'segunda-clave']);
        $this->seed(DocenteSeeder::class);

        $this->assertSame(1, User::where('email', 'docente@example.com')->count());
        $this->assertTrue(Hash::check('primera-clave', $docente->fresh()->password));
    }
}
