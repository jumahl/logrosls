<?php

namespace Tests\Unit\Policies;

use App\Models\EstudianteLogro;
use App\Models\User;
use App\Models\Materia;
use App\Models\Logro;
use App\Policies\EstudianteLogroPolicy;
use Tests\TestCase;

class EstudianteLogropolicyTest extends TestCase
{
    private EstudianteLogroPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new EstudianteLogroPolicy();
    }

    /** @test */
    public function admin_can_view_any_estudiante_logros()
    {
        $admin = $this->createAdmin();
        
        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function profesor_can_view_any_estudiante_logros()
    {
        $profesor = $this->createProfesor();
        
        $this->assertTrue($this->policy->viewAny($profesor));
    }

    /** @test */
    public function profesor_director_can_view_any_estudiante_logros()
    {
        $profesorDirector = $this->createProfesorDirector();
        
        $this->assertTrue($this->policy->viewAny($profesorDirector));
    }

    /** @test */
    public function admin_can_view_specific_estudiante_logro()
    {
        $admin = $this->createAdmin();
        $estudianteLogro = EstudianteLogro::factory()->create();
        
        $this->assertTrue($this->policy->view($admin, $estudianteLogro));
    }

    /** @test */
    public function profesor_can_view_logro_from_their_materia()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $estudianteLogro = EstudianteLogro::factory()->create(['logro_id' => $logro->id]);
        
        $this->assertTrue($this->policy->view($profesor, $estudianteLogro));
    }

    /** @test */
    public function profesor_cannot_view_logro_from_other_materia()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $otroProfesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $estudianteLogro = EstudianteLogro::factory()->create(['logro_id' => $logro->id]);
        
        $this->assertFalse($this->policy->view($profesor, $estudianteLogro));
    }

    /** @test */
    public function admin_can_create_estudiante_logros()
    {
        $admin = $this->createAdmin();
        
        $this->assertTrue($this->policy->create($admin));
    }

    /** @test */
    public function profesor_can_create_estudiante_logros()
    {
        $profesor = $this->createProfesor();
        
        $this->assertTrue($this->policy->create($profesor));
    }

    /** @test */
    public function profesor_director_can_create_estudiante_logros()
    {
        $profesorDirector = $this->createProfesorDirector();
        
        $this->assertTrue($this->policy->create($profesorDirector));
    }

    /** @test */
    public function admin_can_update_any_estudiante_logro()
    {
        $admin = $this->createAdmin();
        $estudianteLogro = EstudianteLogro::factory()->create();
        
        $this->assertTrue($this->policy->update($admin, $estudianteLogro));
    }

    /** @test */
    public function profesor_can_update_logro_from_their_materia()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $estudianteLogro = EstudianteLogro::factory()->create(['logro_id' => $logro->id]);
        
        $this->assertTrue($this->policy->update($profesor, $estudianteLogro));
    }

    /** @test */
    public function profesor_cannot_update_logro_from_other_materia()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $otroProfesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        $estudianteLogro = EstudianteLogro::factory()->create(['logro_id' => $logro->id]);
        
        $this->assertFalse($this->policy->update($profesor, $estudianteLogro));
    }

    /** @test */
    public function only_admin_can_delete_estudiante_logros()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $estudianteLogro = EstudianteLogro::factory()->create();
        
        $this->assertTrue($this->policy->delete($admin, $estudianteLogro));
        $this->assertFalse($this->policy->delete($profesor, $estudianteLogro));
        $this->assertFalse($this->policy->delete($profesorDirector, $estudianteLogro));
    }

    /** @test */
    public function only_admin_can_delete_any_estudiante_logros()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        
        $this->assertTrue($this->policy->deleteAny($admin));
        $this->assertFalse($this->policy->deleteAny($profesor));
        $this->assertFalse($this->policy->deleteAny($profesorDirector));
    }

    /** @test */
    public function profesor_with_multiple_materias_can_access_all_their_logros()
    {
        $profesor = $this->createProfesor();
        $materia1 = Materia::factory()->create(['docente_id' => $profesor->id]);
        $materia2 = Materia::factory()->create(['docente_id' => $profesor->id]);
        
        $logro1 = Logro::factory()->create(['materia_id' => $materia1->id]);
        $logro2 = Logro::factory()->create(['materia_id' => $materia2->id]);
        
        $estudianteLogro1 = EstudianteLogro::factory()->create(['logro_id' => $logro1->id]);
        $estudianteLogro2 = EstudianteLogro::factory()->create(['logro_id' => $logro2->id]);
        
        $this->assertTrue($this->policy->view($profesor, $estudianteLogro1));
        $this->assertTrue($this->policy->view($profesor, $estudianteLogro2));
        $this->assertTrue($this->policy->update($profesor, $estudianteLogro1));
        $this->assertTrue($this->policy->update($profesor, $estudianteLogro2));
    }

    /** @test */
    public function user_without_roles_cannot_access_estudiante_logros()
    {
        $user = User::factory()->create(); // Sin roles asignados
        $estudianteLogro = EstudianteLogro::factory()->create();
        
        $this->assertFalse($this->policy->viewAny($user));
        $this->assertFalse($this->policy->view($user, $estudianteLogro));
        $this->assertFalse($this->policy->create($user));
        $this->assertFalse($this->policy->update($user, $estudianteLogro));
        $this->assertFalse($this->policy->delete($user, $estudianteLogro));
        $this->assertFalse($this->policy->deleteAny($user));
    }

    /** @test */
    public function policy_correctly_identifies_profesor_materias()
    {
        $profesor = $this->createProfesor();
        
        // Crear materias donde el profesor NO es docente
        $materiaAjena1 = Materia::factory()->create();
        $materiaAjena2 = Materia::factory()->create();
        
        // Crear materias donde el profesor SÍ es docente
        $materiaPropia1 = Materia::factory()->create(['docente_id' => $profesor->id]);
        $materiaPropia2 = Materia::factory()->create(['docente_id' => $profesor->id]);
        
        // Verificar que el profesor tiene las materias correctas
        $materiasProfesor = $profesor->materias->pluck('id');
        $this->assertTrue($materiasProfesor->contains($materiaPropia1->id));
        $this->assertTrue($materiasProfesor->contains($materiaPropia2->id));
        $this->assertFalse($materiasProfesor->contains($materiaAjena1->id));
        $this->assertFalse($materiasProfesor->contains($materiaAjena2->id));
    }
}
