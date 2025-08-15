<?php

namespace Tests\Unit\Policies;

use App\Models\Materia;
use App\Models\User;
use App\Policies\MateriaPolicy;
use Tests\TestCase;

class MateriaPolicyTest extends TestCase
{
    private MateriaPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new MateriaPolicy();
    }

    /** @test */
    public function anyone_can_view_any_materias()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        
        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->viewAny($profesor));
        $this->assertTrue($this->policy->viewAny($profesorDirector));
        $this->assertTrue($this->policy->viewAny($userWithoutRole));
    }

    /** @test */
    public function anyone_can_view_specific_materia()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        $materia = Materia::factory()->create();
        
        $this->assertTrue($this->policy->view($admin, $materia));
        $this->assertTrue($this->policy->view($profesor, $materia));
        $this->assertTrue($this->policy->view($profesorDirector, $materia));
        $this->assertTrue($this->policy->view($userWithoutRole, $materia));
    }

    /** @test */
    public function only_admin_can_create_materias()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        
        $this->assertTrue($this->policy->create($admin));
        $this->assertFalse($this->policy->create($profesor));
        $this->assertFalse($this->policy->create($profesorDirector));
        $this->assertFalse($this->policy->create($userWithoutRole));
    }

    /** @test */
    public function only_admin_can_update_materias()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        $materia = Materia::factory()->create();
        
        $this->assertTrue($this->policy->update($admin, $materia));
        $this->assertFalse($this->policy->update($profesor, $materia));
        $this->assertFalse($this->policy->update($profesorDirector, $materia));
        $this->assertFalse($this->policy->update($userWithoutRole, $materia));
    }

    /** @test */
    public function only_admin_can_delete_materias()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        $materia = Materia::factory()->create();
        
        $this->assertTrue($this->policy->delete($admin, $materia));
        $this->assertFalse($this->policy->delete($profesor, $materia));
        $this->assertFalse($this->policy->delete($profesorDirector, $materia));
        $this->assertFalse($this->policy->delete($userWithoutRole, $materia));
    }

    /** @test */
    public function only_admin_can_delete_any_materias()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        
        $this->assertTrue($this->policy->deleteAny($admin));
        $this->assertFalse($this->policy->deleteAny($profesor));
        $this->assertFalse($this->policy->deleteAny($profesorDirector));
        $this->assertFalse($this->policy->deleteAny($userWithoutRole));
    }

    /** @test */
    public function policy_is_consistent_across_all_admin_methods()
    {
        $admin = $this->createAdmin();
        $materia = Materia::factory()->create();
        
        // Admin debe poder hacer todo
        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->view($admin, $materia));
        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->update($admin, $materia));
        $this->assertTrue($this->policy->delete($admin, $materia));
        $this->assertTrue($this->policy->deleteAny($admin));
    }

    /** @test */
    public function policy_is_consistent_for_non_admin_users()
    {
        $profesor = $this->createProfesor();
        $materia = Materia::factory()->create();
        
        // Profesor puede ver pero no modificar
        $this->assertTrue($this->policy->viewAny($profesor));
        $this->assertTrue($this->policy->view($profesor, $materia));
        $this->assertFalse($this->policy->create($profesor));
        $this->assertFalse($this->policy->update($profesor, $materia));
        $this->assertFalse($this->policy->delete($profesor, $materia));
        $this->assertFalse($this->policy->deleteAny($profesor));
    }

    /** @test */
    public function profesor_can_view_their_own_materia()
    {
        $profesor = $this->createProfesor();
        $materiaPropia = Materia::factory()->create(['docente_id' => $profesor->id]);
        
        // Aunque la política permite a todos ver materias,
        // verificamos que el profesor puede ver específicamente la suya
        $this->assertTrue($this->policy->view($profesor, $materiaPropia));
    }

    /** @test */
    public function profesor_cannot_modify_their_own_materia()
    {
        $profesor = $this->createProfesor();
        $materiaPropia = Materia::factory()->create(['docente_id' => $profesor->id]);
        
        // Aunque sea su materia, no puede modificarla (solo admin)
        $this->assertTrue($this->policy->view($profesor, $materiaPropia));
        $this->assertFalse($this->policy->update($profesor, $materiaPropia));
        $this->assertFalse($this->policy->delete($profesor, $materiaPropia));
    }

    /** @test */
    public function admin_can_modify_any_materia_regardless_of_docente()
    {
        $admin = $this->createAdmin();
        $profesor1 = $this->createProfesor();
        $profesor2 = $this->createProfesor();
        
        $materia1 = Materia::factory()->create(['docente_id' => $profesor1->id]);
        $materia2 = Materia::factory()->create(['docente_id' => $profesor2->id]);
        $materiaSinDocente = Materia::factory()->create(['docente_id' => null]);
        
        // Admin puede modificar cualquier materia
        $this->assertTrue($this->policy->update($admin, $materia1));
        $this->assertTrue($this->policy->update($admin, $materia2));
        $this->assertTrue($this->policy->update($admin, $materiaSinDocente));
        
        $this->assertTrue($this->policy->delete($admin, $materia1));
        $this->assertTrue($this->policy->delete($admin, $materia2));
        $this->assertTrue($this->policy->delete($admin, $materiaSinDocente));
    }
}
