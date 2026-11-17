<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Casos de registro-pruebas.md. Se ejecutan sobre la base registro_laravel_test
 * (ver phpunit.xml). Se comprueba el EFECTO EN LA BASE, no solo el código HTTP.
 */
class EstudianteTest extends TestCase
{
    use RefreshDatabase;

    private User $docente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->docente = User::factory()->create(['email' => 'docente@example.com']);
    }

    private function datosValidos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'apellido' => 'Prueba',
            'identidad' => '0801200012345',
            'correo' => 'ana.prueba@example.com',
            'edad' => 20,
            'programa' => 'Ingeniería de Sistemas',
        ], $cambios);
    }

    /** Caso 1 */
    public function test_alta_valida_crea_una_fila_y_regresa_al_listado(): void
    {
        $this->actingAs($this->docente)
            ->post('/estudiantes', $this->datosValidos())
            ->assertRedirect('/estudiantes')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('estudiantes', 1);
        $this->assertDatabaseHas('estudiantes', ['correo' => 'ana.prueba@example.com', 'edad' => 20]);
    }

    /** Caso 2 */
    public function test_correo_ya_registrado_se_rechaza_sin_duplicar(): void
    {
        Estudiante::factory()->create(['correo' => 'ana.prueba@example.com']);

        $this->actingAs($this->docente)
            ->from('/estudiantes/create')
            ->post('/estudiantes', $this->datosValidos(['nombre' => 'Otra']))
            ->assertRedirect('/estudiantes/create')
            ->assertSessionHasErrors(['correo' => 'Ese correo ya está registrado.']);

        $this->assertDatabaseCount('estudiantes', 1);
    }

    /** Caso 3 */
    public function test_edad_15_o_no_entera_se_rechaza_y_no_se_guarda(): void
    {
        foreach ([15, '17.5', 'diecisiete', ''] as $edad) {
            $this->actingAs($this->docente)
                ->post('/estudiantes', $this->datosValidos(['edad' => $edad]))
                ->assertSessionHasErrors('edad');
        }

        $this->assertDatabaseCount('estudiantes', 0);
    }

    /** Caso 4: petición controlada que se salta el <select> del formulario */
    public function test_programa_inventado_se_rechaza_por_el_catalogo(): void
    {
        $this->actingAs($this->docente)
            ->post('/estudiantes', $this->datosValidos(['programa' => 'Astrología Avanzada']))
            ->assertSessionHasErrors(['programa' => 'Selecciona un programa del catálogo.']);

        $this->assertDatabaseCount('estudiantes', 0);
    }

    /** Caso 5 */
    public function test_editar_conservando_el_propio_correo_guarda_sin_conflicto(): void
    {
        $ana = Estudiante::factory()->create($this->datosValidos());

        $this->actingAs($this->docente)
            ->put("/estudiantes/{$ana->id}", $this->datosValidos(['nombre' => 'Ana María']))
            ->assertRedirect('/estudiantes')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('estudiantes', 1);
        $this->assertDatabaseHas('estudiantes', ['id' => $ana->id, 'nombre' => 'Ana María', 'correo' => 'ana.prueba@example.com']);
    }

    /** Caso 6 */
    public function test_id_inexistente_no_modifica_otra_fila_ni_crea_registro(): void
    {
        $ana = Estudiante::factory()->create($this->datosValidos());
        $inexistente = $ana->id + 999;

        $this->actingAs($this->docente)->get("/estudiantes/{$inexistente}/edit")->assertNotFound();
        $this->actingAs($this->docente)
            ->put("/estudiantes/{$inexistente}", $this->datosValidos(['nombre' => 'Intruso', 'correo' => 'intruso@example.com']))
            ->assertNotFound();
        $this->actingAs($this->docente)->delete("/estudiantes/{$inexistente}")->assertNotFound();

        $this->assertDatabaseCount('estudiantes', 1);
        $this->assertDatabaseHas('estudiantes', ['id' => $ana->id, 'nombre' => 'Ana']);
        $this->assertDatabaseMissing('estudiantes', ['correo' => 'intruso@example.com']);
    }

    /** Caso 6b: un "id" extra en el formulario no cambia la fila editada ($fillable) */
    public function test_id_enviado_en_el_formulario_se_ignora(): void
    {
        $ana = Estudiante::factory()->create($this->datosValidos());
        $otra = Estudiante::factory()->create();

        $this->actingAs($this->docente)
            ->put("/estudiantes/{$ana->id}", $this->datosValidos(['id' => $otra->id, 'nombre' => 'Ana Editada']));

        $this->assertDatabaseHas('estudiantes', ['id' => $ana->id, 'nombre' => 'Ana Editada']);
        $this->assertDatabaseHas('estudiantes', ['id' => $otra->id, 'nombre' => $otra->nombre]);
    }

    /** Caso 7 */
    public function test_busqueda_sin_coincidencias_muestra_mensaje_comprensible(): void
    {
        Estudiante::factory()->create($this->datosValidos());

        $this->actingAs($this->docente)
            ->get('/estudiantes?buscar=Zzyzx')
            ->assertOk()
            ->assertSee('No se encontraron estudiantes cuyo nombre o apellido contenga', false)
            ->assertDontSee('ana.prueba@example.com');
    }

    /** Caso 7b: búsqueda por parte del nombre */
    public function test_busqueda_por_parte_del_nombre_encuentra_coincidencias(): void
    {
        Estudiante::factory()->create($this->datosValidos());
        Estudiante::factory()->create(['nombre' => 'Bruno', 'apellido' => 'Ficticio', 'correo' => 'bruno@example.com']);

        $this->actingAs($this->docente)
            ->get('/estudiantes?buscar=An')
            ->assertOk()
            ->assertSee('ana.prueba@example.com')
            ->assertDontSee('bruno@example.com');
    }

    /** Caso 8 */
    public function test_cancelar_la_confirmacion_no_borra_la_fila(): void
    {
        $ana = Estudiante::factory()->create($this->datosValidos());

        // Paso 1: la confirmación (GET) solo muestra la pantalla.
        $this->actingAs($this->docente)
            ->get("/estudiantes/{$ana->id}/eliminar")
            ->assertOk()
            ->assertSee('¿Eliminar este estudiante?', false)
            ->assertSee('Cancelar');

        // "Cancelar" es un enlace al listado.
        $this->actingAs($this->docente)->get('/estudiantes')->assertOk()->assertSee('ana.prueba@example.com');

        $this->assertDatabaseHas('estudiantes', ['id' => $ana->id]);
    }

    /** Caso 9 */
    public function test_confirmar_borrado_elimina_solo_esa_fila(): void
    {
        $ana = Estudiante::factory()->create($this->datosValidos());
        $otra = Estudiante::factory()->create();

        $this->actingAs($this->docente)
            ->delete("/estudiantes/{$ana->id}")
            ->assertRedirect('/estudiantes');

        $this->assertDatabaseMissing('estudiantes', ['id' => $ana->id]);
        $this->assertDatabaseHas('estudiantes', ['id' => $otra->id]);
        $this->assertDatabaseCount('estudiantes', 1);
    }

    /** Caso 10 */
    public function test_rutas_protegidas_sin_sesion_exigen_acceso(): void
    {
        $ana = Estudiante::factory()->create($this->datosValidos());

        $this->get('/estudiantes')->assertRedirect('/login');
        $this->get('/estudiantes/create')->assertRedirect('/login');
        $this->get("/estudiantes/{$ana->id}/edit")->assertRedirect('/login');
        $this->get("/estudiantes/{$ana->id}/eliminar")->assertRedirect('/login');
        $this->post('/estudiantes', $this->datosValidos(['correo' => 'nuevo@example.com']))->assertRedirect('/login');
        $this->delete("/estudiantes/{$ana->id}")->assertRedirect('/login');

        $this->assertDatabaseCount('estudiantes', 1);
        $this->assertDatabaseHas('estudiantes', ['id' => $ana->id]);
    }

    /** Caso 12 */
    public function test_signos_especiales_se_muestran_como_texto(): void
    {
        $this->actingAs($this->docente)
            ->post('/estudiantes', $this->datosValidos(['nombre' => '<b>Ana</b> & Co']))
            ->assertSessionHasNoErrors();

        // En la base se guarda tal cual lo escribió el usuario...
        $this->assertDatabaseHas('estudiantes', ['nombre' => '<b>Ana</b> & Co']);

        // ...y Blade lo escapa al mostrarlo: no se interpreta como negrita.
        $this->actingAs($this->docente)
            ->get('/estudiantes')
            ->assertSee('&lt;b&gt;Ana&lt;/b&gt; &amp; Co', false)
            ->assertDontSee('<b>Ana</b>', false);
    }
}
