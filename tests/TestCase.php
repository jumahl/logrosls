<?php

namespace Tests;

use App\Models\User;
use App\Models\Grado;
use App\Models\Materia;
use App\Models\Estudiante;
use App\Models\Logro;
use App\Models\Periodo;
use App\Models\EstudianteLogro;
use App\Models\DesempenoMateria;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles y permisos básicos
        $this->setupRolesAndPermissions();
        
        // Crear años escolares básicos para testing
        $this->setupAniosEscolares();
    }

    /**
     * Setup basic school years for testing.
     */
    protected function setupAniosEscolares(): void
    {
        \App\Models\AnioEscolar::factory()->create([
            'anio' => 2024,
            'activo' => false,
            'finalizado' => true,
        ]);
        
        \App\Models\AnioEscolar::factory()->create([
            'anio' => 2025, 
            'activo' => true,
            'finalizado' => false,
        ]);
        
        \App\Models\AnioEscolar::factory()->create([
            'anio' => 2020,
            'activo' => false,
            'finalizado' => true,
        ]);
        
        \App\Models\AnioEscolar::factory()->create([
            'anio' => 2021,
            'activo' => false,
            'finalizado' => true,
        ]);
        
        \App\Models\AnioEscolar::factory()->create([
            'anio' => 2022,
            'activo' => false,
            'finalizado' => true,
        ]);
        
        \App\Models\AnioEscolar::factory()->create([
            'anio' => 2023,
            'activo' => false,
            'finalizado' => true,
        ]);
    }

    /**
     * Setup roles and permissions for testing.
     */
    protected function setupRolesAndPermissions(): void
    {
        // Crear roles básicos
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'profesor']);

        // Crear algunos permisos básicos
        $permissions = [
            'view_estudiantes',
            'create_estudiantes',
            'update_estudiantes',
            'delete_estudiantes',
            'view_logros',
            'create_logros',
            'update_logros',
            'delete_logros',
            'view_materias',
            'create_materias',
            'update_materias',
            'delete_materias',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Asignar todos los permisos al admin
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());
    }

    /**
     * Create a user with admin role.
     */
    protected function createAdmin(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('admin');
        return $user;
    }

    /**
     * Create a user with profesor role.
     */
    protected function createProfesor(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole('profesor');
        return $user;
    }

    /**
     * Create a user with profesor role and assign as director de grupo.
     */
    protected function createProfesorDirector(array $attributes = []): User
    {
        // Crear o usar un grado existente
        $grado = $attributes['grado'] ?? Grado::factory()->create();
        unset($attributes['grado']); // Remover del array para no pasarlo al factory
        
        $user = User::factory()->create(array_merge($attributes, [
            'director_grado_id' => $grado->id
        ]));
        $user->assignRole('profesor');
        return $user;
    }

    /**
     * Create a complete academic setup for testing.
     * Returns an array with all created models.
     */
    protected function createAcademicSetup(): array
    {
        // Crear grados
        $grados = [
            'primero' => Grado::factory()->primaria()->create(['nombre' => 'Primero']),
            'segundo' => Grado::factory()->primaria()->create(['nombre' => 'Segundo']),
            'sexto' => Grado::factory()->secundaria()->create(['nombre' => 'Sexto']),
        ];

        // Crear usuarios
        $admin = $this->createAdmin(['name' => 'Admin Test']);
        $profesor1 = $this->createProfesor(['name' => 'Profesor Matemáticas']);
        $profesor2 = $this->createProfesor(['name' => 'Profesor Lenguaje']);
        $profesorDirector = $this->createProfesorDirector(['name' => 'Profesor Director']);

        // Crear materias
        $materias = [
            'matematicas' => Materia::factory()->create([
                'nombre' => 'Matemáticas',
                'docente_id' => $profesor1->id,
            ]),
            'lenguaje' => Materia::factory()->create([
                'nombre' => 'Lenguaje',
                'docente_id' => $profesor2->id,
            ]),
        ];

        // Asociar materias con grados
        foreach ($grados as $grado) {
            $grado->materias()->attach(array_column($materias, 'id'));
        }

        // Crear estudiantes
        $estudiantes = [
            'juan' => Estudiante::factory()->create([
                'nombre' => 'Juan',
                'apellido' => 'Pérez García',
                'grado_id' => $grados['primero']->id,
            ]),
            'maria' => Estudiante::factory()->create([
                'nombre' => 'María',
                'apellido' => 'González López',
                'grado_id' => $grados['segundo']->id,
            ]),
        ];

        // Crear logros
        $logros = [
            'matematicas_basico' => Logro::factory()->basico()->create([
                'titulo' => 'Suma y resta básica',
                'materia_id' => $materias['matematicas']->id,
            ]),
            'lenguaje_lectura' => Logro::factory()->basico()->create([
                'titulo' => 'Lectura comprensiva',
                'materia_id' => $materias['lenguaje']->id,
            ]),
        ];

        // Crear períodos
        $periodos = [
            'periodo1' => Periodo::factory()->primerPeriodo()->primerCorte()->create([
                'anio_escolar' => 2025, // Usar año activo
            ]),
            'periodo2' => Periodo::factory()->primerPeriodo()->segundoCorte()->create([
                'anio_escolar' => 2025, // Usar año activo
            ]),
        ];

        return compact('grados', 'admin', 'profesor1', 'profesor2', 'profesorDirector', 'materias', 'estudiantes', 'logros', 'periodos');
    }

    /**
     * Create a student with logros for testing.
     */
    protected function createStudentWithLogros(): array
    {
        $setup = $this->createAcademicSetup();
        
        // Crear desempeños de materia para el estudiante
        $desempenoMatematicas = DesempenoMateria::factory()->create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'materia_id' => $setup['materias']['matematicas']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'E',
        ]);
        
        $desempenoLenguaje = DesempenoMateria::factory()->create([
            'estudiante_id' => $setup['estudiantes']['juan']->id,
            'materia_id' => $setup['materias']['lenguaje']->id,
            'periodo_id' => $setup['periodos']['periodo1']->id,
            'nivel_desempeno' => 'S',
        ]);

        // Crear logros asociados a los desempeños
        $estudianteLogros = [
            EstudianteLogro::factory()->create([
                'desempeno_materia_id' => $desempenoMatematicas->id,
                'logro_id' => $setup['logros']['matematicas_basico']->id,
                'alcanzado' => true,
            ]),
            EstudianteLogro::factory()->create([
                'desempeno_materia_id' => $desempenoLenguaje->id,
                'logro_id' => $setup['logros']['lenguaje_lectura']->id,
                'alcanzado' => true,
            ]),
        ];

        $setup['estudianteLogros'] = $estudianteLogros;
        $setup['desempenosMateria'] = [$desempenoMatematicas, $desempenoLenguaje];
        
        return $setup;
    }

    /**
     * Assert that a model has the expected relationships.
     */
    protected function assertHasRelation($model, string $relation, ?string $relatedModel = null): void
    {
        $this->assertTrue(method_exists($model, $relation), "Relation {$relation} does not exist on " . get_class($model));
        
        if ($relatedModel) {
            $relationResult = $model->$relation();
            $this->assertInstanceOf($relatedModel, $relationResult->getRelated());
        }
    }

    /**
     * Assert that a model has specific fillable attributes.
     */
    protected function assertHasFillable($model, array $expectedFillable): void
    {
        $actualFillable = $model->getFillable();
        
        foreach ($expectedFillable as $attribute) {
            $this->assertContains($attribute, $actualFillable, "Attribute {$attribute} is not fillable");
        }
    }

    /**
     * Assert that a model has specific casts.
     */
    protected function assertHasCasts($model, array $expectedCasts): void
    {
        $actualCasts = $model->getCasts();
        
        foreach ($expectedCasts as $attribute => $cast) {
            $this->assertArrayHasKey($attribute, $actualCasts, "Cast for {$attribute} does not exist");
            $this->assertEquals($cast, $actualCasts[$attribute], "Cast for {$attribute} is not {$cast}");
        }
    }

    /**
     * Create multiple records using factory.
     */
    protected function createMultiple(string $factoryClass, int $count, array $attributes = []): \Illuminate\Database\Eloquent\Collection
    {
        return $factoryClass::factory($count)->create($attributes);
    }

    /**
     * Assert model validation fails for specific attributes.
     */
    protected function assertValidationFails($model, array $invalidData, array $expectedErrors = []): void
    {
        try {
            $model->fill($invalidData)->save();
            $this->fail('Expected validation to fail but it passed');
        } catch (\Exception $e) {
            $this->assertTrue(true);
            
            if (!empty($expectedErrors)) {
                foreach ($expectedErrors as $field) {
                    $this->assertStringContainsString($field, $e->getMessage());
                }
            }
        }
    }
}