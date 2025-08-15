<?php

namespace Tests\Unit\Policies;

use App\Models\Logro;
use App\Models\User;
use App\Models\Materia;
use App\Policies\LogroPolicy;
use Tests\TestCase;

class LogroPolicyTest extends TestCase
{
    private LogroPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new LogroPolicy();
    }

    /** @test */
    public function admin_can_view_any_logros()
    {
        $admin = $this->createAdmin();
        
        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function profesor_can_view_any_logros()
    {
        $profesor = $this->createProfesor();
        
        $this->assertTrue($this->policy->viewAny($profesor));
    }

    /** @test */
    public function profesor_director_can_view_any_logros()
    {
        $profesorDirector = $this->createProfesorDirector();
        
        $this->assertTrue($this->policy->viewAny($profesorDirector));
    }

    /** @test */
    public function admin_can_view_specific_logro()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create();
        
        $this->assertTrue($this->policy->view($admin, $logro));
    }

    /** @test */
    public function profesor_can_view_logro_from_their_materia()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        
        $this->assertTrue($this->policy->view($profesor, $logro));
    }

    /** @test */
    public function profesor_cannot_view_logro_from_other_materia()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $otroProfesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        
        $this->assertFalse($this->policy->view($profesor, $logro));
    }

    /** @test */
    public function admin_can_create_logros()
    {
        $admin = $this->createAdmin();
        
        $this->assertTrue($this->policy->create($admin));
    }

    /** @test */
    public function profesor_can_create_logros()
    {
        $profesor = $this->createProfesor();
        
        $this->assertTrue($this->policy->create($profesor));
    }

    /** @test */
    public function profesor_director_can_create_logros()
    {
        $profesorDirector = $this->createProfesorDirector();
        
        $this->assertTrue($this->policy->create($profesorDirector));
    }

    /** @test */
    public function admin_can_update_any_logro()
    {
        $admin = $this->createAdmin();
        $logro = Logro::factory()->create();
        
        $this->assertTrue($this->policy->update($admin, $logro));
    }

    /** @test */
    public function profesor_can_update_logro_from_their_materia()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        
        $this->assertTrue($this->policy->update($profesor, $logro));
    }

    /** @test */
    public function profesor_cannot_update_logro_from_other_materia()
    {
        $profesor = $this->createProfesor();
        $otroProfesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $otroProfesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        
        $this->assertFalse($this->policy->update($profesor, $logro));
    }

    /** @test */
    public function only_admin_can_delete_logros()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $logro = Logro::factory()->create();
        
        $this->assertTrue($this->policy->delete($admin, $logro));
        $this->assertFalse($this->policy->delete($profesor, $logro));
        $this->assertFalse($this->policy->delete($profesorDirector, $logro));
    }

    /** @test */
    public function only_admin_can_delete_any_logros()
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
        
        $this->assertTrue($this->policy->view($profesor, $logro1));
        $this->assertTrue($this->policy->view($profesor, $logro2));
        $this->assertTrue($this->policy->update($profesor, $logro1));
        $this->assertTrue($this->policy->update($profesor, $logro2));
    }

    /** @test */
    public function user_without_roles_cannot_access_logros()
    {
        $user = User::factory()->create(); // Sin roles asignados
        $logro = Logro::factory()->create();
        
        $this->assertFalse($this->policy->viewAny($user));
        $this->assertFalse($this->policy->view($user, $logro));
        $this->assertFalse($this->policy->create($user));
        $this->assertFalse($this->policy->update($user, $logro));
        $this->assertFalse($this->policy->delete($user, $logro));
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
        
        // Crear logros para cada materia
        $logroAjeno1 = Logro::factory()->create(['materia_id' => $materiaAjena1->id]);
        $logroAjeno2 = Logro::factory()->create(['materia_id' => $materiaAjena2->id]);
        $logroPropio1 = Logro::factory()->create(['materia_id' => $materiaPropia1->id]);
        $logroPropio2 = Logro::factory()->create(['materia_id' => $materiaPropia2->id]);
        
        // Verificar accesos
        $this->assertFalse($this->policy->view($profesor, $logroAjeno1));
        $this->assertFalse($this->policy->view($profesor, $logroAjeno2));
        $this->assertTrue($this->policy->view($profesor, $logroPropio1));
        $this->assertTrue($this->policy->view($profesor, $logroPropio2));
        
        $this->assertFalse($this->policy->update($profesor, $logroAjeno1));
        $this->assertFalse($this->policy->update($profesor, $logroAjeno2));
        $this->assertTrue($this->policy->update($profesor, $logroPropio1));
        $this->assertTrue($this->policy->update($profesor, $logroPropio2));
    }

    /** @test */
    public function admin_has_full_access_to_all_logros()
    {
        $admin = $this->createAdmin();
        $profesor1 = $this->createProfesor();
        $profesor2 = $this->createProfesor();
        
        $materia1 = Materia::factory()->create(['docente_id' => $profesor1->id]);
        $materia2 = Materia::factory()->create(['docente_id' => $profesor2->id]);
        
        $logro1 = Logro::factory()->create(['materia_id' => $materia1->id]);
        $logro2 = Logro::factory()->create(['materia_id' => $materia2->id]);
        
        // Admin puede acceder a logros de cualquier profesor
        $this->assertTrue($this->policy->view($admin, $logro1));
        $this->assertTrue($this->policy->view($admin, $logro2));
        $this->assertTrue($this->policy->update($admin, $logro1));
        $this->assertTrue($this->policy->update($admin, $logro2));
        $this->assertTrue($this->policy->delete($admin, $logro1));
        $this->assertTrue($this->policy->delete($admin, $logro2));
    }

    /** @test */
    public function profesor_permissions_are_consistent()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create(['docente_id' => $profesor->id]);
        $logro = Logro::factory()->create(['materia_id' => $materia->id]);
        
        // Si puede ver, también debe poder actualizar (pero no eliminar)
        $this->assertTrue($this->policy->view($profesor, $logro));
        $this->assertTrue($this->policy->update($profesor, $logro));
        $this->assertFalse($this->policy->delete($profesor, $logro));
        
        // Puede crear pero no eliminar en masa
        $this->assertTrue($this->policy->create($profesor));
        $this->assertFalse($this->policy->deleteAny($profesor));
    }
}
