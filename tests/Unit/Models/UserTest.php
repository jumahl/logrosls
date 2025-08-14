<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Grado;
use App\Models\Materia;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserTest extends TestCase
{
    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $expectedFillable = ['name', 'email', 'password', 'director_grado_id'];
        $user = new User();
        
        $this->assertHasFillable($user, $expectedFillable);
    }

    /** @test */
    public function it_has_correct_hidden_attributes()
    {
        $user = new User();
        $hidden = $user->getHidden();
        
        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $expectedCasts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'id' => 'int',
        ];
        $user = new User();
        
        $this->assertHasCasts($user, $expectedCasts);
    }

    /** @test */
    public function it_can_create_user_with_factory()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /** @test */
    public function it_generates_correct_initials()
    {
        $user = User::factory()->create(['name' => 'Juan Carlos Pérez García']);
        
        $this->assertEquals('JC', $user->initials());
    }

    /** @test */
    public function it_generates_initials_for_single_name()
    {
        $user = User::factory()->create(['name' => 'Maria']);
        
        $this->assertEquals('M', $user->initials());
    }

    /** @test */
    public function it_has_materias_relationship()
    {
        $user = User::factory()->create();
        $materias = Materia::factory(2)->create(['docente_id' => $user->id]);

        $this->assertCount(2, $user->materias);
        $this->assertTrue($user->materias->contains($materias[0]));
    }

    /** @test */
    public function it_has_director_grado_relationship()
    {
        $grado = Grado::factory()->create(['nombre' => 'Primero']);
        $user = User::factory()->create(['director_grado_id' => $grado->id]);

        $this->assertNotNull($user->directorGrado);
        $this->assertEquals('Primero', $user->directorGrado->nombre);
        $this->assertInstanceOf(Grado::class, $user->directorGrado);
    }

    /** @test */
    public function is_director_grupo_returns_true_when_has_director_grado_id()
    {
        $grado = Grado::factory()->create();
        $user = User::factory()->create(['director_grado_id' => $grado->id]);

        $this->assertTrue($user->isDirectorGrupo());
    }

    /** @test */
    public function is_director_grupo_returns_false_when_no_director_grado_id()
    {
        $user = User::factory()->create(['director_grado_id' => null]);

        $this->assertFalse($user->isDirectorGrupo());
    }

    /** @test */
    public function estudiantes_grupo_returns_estudiantes_when_is_director()
    {
        $grado = Grado::factory()->create();
        $user = User::factory()->create(['director_grado_id' => $grado->id]);
        
        // Crear estudiantes en el grado
        $estudiantes = \App\Models\Estudiante::factory(3)->create(['grado_id' => $grado->id]);

        $estudiantesGrupo = $user->estudiantesGrupo();
        
        $this->assertCount(3, $estudiantesGrupo);
        $this->assertTrue($estudiantesGrupo->contains($estudiantes[0]));
    }

    /** @test */
    public function estudiantes_grupo_returns_empty_collection_when_not_director()
    {
        $user = User::factory()->create(['director_grado_id' => null]);

        $estudiantesGrupo = $user->estudiantesGrupo();
        
        $this->assertCount(0, $estudiantesGrupo);
    }

    /** @test */
    public function factory_can_create_director_grado()
    {
        $grado = Grado::factory()->create();
        $user = User::factory()->directorGrado($grado)->create();

        $this->assertEquals($grado->id, $user->director_grado_id);
        $this->assertTrue($user->isDirectorGrupo());
    }

    /** @test */
    public function factory_can_create_admin_user()
    {
        $user = User::factory()->admin()->create();

        $this->assertEquals('Admin User', $user->name);
        $this->assertEquals('admin@test.com', $user->email);
        $this->assertTrue($user->hasRole('admin'));
    }

    /** @test */
    public function factory_can_create_profesor_user()
    {
        $user = User::factory()->profesor()->create();

        $this->assertStringContainsString('Profesor', $user->name);
        $this->assertTrue($user->hasRole('profesor'));
    }

    /** @test */
    public function factory_can_create_profesor_director_user()
    {
        $grado = Grado::factory()->create();
        $user = User::factory()->profesor()->create(['director_grado_id' => $grado->id]);

        $this->assertStringContainsString('Profesor', $user->name);
        $this->assertTrue($user->hasRole('profesor'));
        $this->assertTrue($user->isDirectorGrupo());
        $this->assertEquals($grado->id, $user->director_grado_id);
    }

    /** @test */
    public function factory_can_create_with_custom_password()
    {
        $user = User::factory()->withPassword('custom123')->create();

        $this->assertTrue(Hash::check('custom123', $user->password));
    }

    /** @test */
    public function factory_can_create_with_test_password()
    {
        $user = User::factory()->withTestPassword()->create();

        $this->assertTrue(Hash::check('test123', $user->password));
    }

    /** @test */
    public function factory_can_create_unverified_user()
    {
        $user = User::factory()->unverified()->create();

        $this->assertNull($user->email_verified_at);
    }

    /** @test */
    public function email_is_unique()
    {
        User::factory()->create(['email' => 'test@example.com']);
        
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function password_is_automatically_hashed()
    {
        $user = User::factory()->create();
        
        // El password no debería ser texto plano
        $this->assertNotEquals('password', $user->password);
        // Pero debería coincidir con Hash::check
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /** @test */
    public function director_grado_id_is_optional()
    {
        $user = User::factory()->create(['director_grado_id' => null]);
        
        $this->assertNull($user->director_grado_id);
        $this->assertNull($user->directorGrado);
    }

    /** @test */
    public function it_requires_name_and_email()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::create([
            'password' => Hash::make('password'),
        ]);
    }

    /** @test */
    public function remember_token_is_generated_by_factory()
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->remember_token);
        $this->assertEquals(10, strlen($user->remember_token));
    }

    /** @test */
    public function traits_are_loaded_correctly()
    {
        $user = new User();
        
        // Verificar que tiene el trait HasRoles de Spatie
        $this->assertTrue(method_exists($user, 'assignRole'));
        $this->assertTrue(method_exists($user, 'hasRole'));
        
        // Verificar que tiene el trait HasFactory
        $this->assertTrue(method_exists($user, 'factory'));
        
        // Verificar que tiene el trait Notifiable
        $this->assertTrue(method_exists($user, 'notify'));
    }
}
