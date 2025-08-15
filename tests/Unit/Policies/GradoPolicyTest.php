<?php

namespace Tests\Unit\Policies;

use App\Models\Grado;
use App\Models\User;
use App\Policies\GradoPolicy;
use Tests\TestCase;

class GradoPolicyTest extends TestCase
{
    private GradoPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new GradoPolicy();
    }

    /** @test */
    public function anyone_can_view_any_grados()
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
    public function anyone_can_view_specific_grado()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        $grado = Grado::factory()->create();
        
        $this->assertTrue($this->policy->view($admin, $grado));
        $this->assertTrue($this->policy->view($profesor, $grado));
        $this->assertTrue($this->policy->view($profesorDirector, $grado));
        $this->assertTrue($this->policy->view($userWithoutRole, $grado));
    }

    /** @test */
    public function only_admin_can_create_grados()
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
    public function only_admin_can_update_grados()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        $grado = Grado::factory()->create();
        
        $this->assertTrue($this->policy->update($admin, $grado));
        $this->assertFalse($this->policy->update($profesor, $grado));
        $this->assertFalse($this->policy->update($profesorDirector, $grado));
        $this->assertFalse($this->policy->update($userWithoutRole, $grado));
    }

    /** @test */
    public function only_admin_can_delete_grados()
    {
        $admin = $this->createAdmin();
        $profesor = $this->createProfesor();
        $profesorDirector = $this->createProfesorDirector();
        $userWithoutRole = User::factory()->create();
        $grado = Grado::factory()->create();
        
        $this->assertTrue($this->policy->delete($admin, $grado));
        $this->assertFalse($this->policy->delete($profesor, $grado));
        $this->assertFalse($this->policy->delete($profesorDirector, $grado));
        $this->assertFalse($this->policy->delete($userWithoutRole, $grado));
    }

    /** @test */
    public function only_admin_can_delete_any_grados()
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
        $grado = Grado::factory()->create();
        
        // Admin debe poder hacer todo
        $this->assertTrue($this->policy->viewAny($admin));
        $this->assertTrue($this->policy->view($admin, $grado));
        $this->assertTrue($this->policy->create($admin));
        $this->assertTrue($this->policy->update($admin, $grado));
        $this->assertTrue($this->policy->delete($admin, $grado));
        $this->assertTrue($this->policy->deleteAny($admin));
    }

    /** @test */
    public function policy_is_consistent_for_non_admin_users()
    {
        $profesor = $this->createProfesor();
        $grado = Grado::factory()->create();
        
        // Profesor puede ver pero no modificar
        $this->assertTrue($this->policy->viewAny($profesor));
        $this->assertTrue($this->policy->view($profesor, $grado));
        $this->assertFalse($this->policy->create($profesor));
        $this->assertFalse($this->policy->update($profesor, $grado));
        $this->assertFalse($this->policy->delete($profesor, $grado));
        $this->assertFalse($this->policy->deleteAny($profesor));
    }
}
