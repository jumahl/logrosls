<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class ShieldPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $profesorRole = Role::firstOrCreate(['name' => 'profesor']);

        // Crear permisos para cada recurso
        $resources = [
            'grado', 'periodo', 'estudiante', 'materia', 'logro', 'estudiante_logro', 'user'
        ];

        $actions = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => $resource . '.' . $action]);
            }
        }

        // Asignar todos los permisos al rol de admin
        $adminRole->givePermissionTo(Permission::all());

        // Asignar permisos específicos al rol de profesor
        $profesorPermissions = [
            // Solo puede ver grados y periodos
            'grado.view',
            'grado.view_any',
            'periodo.view',
            'periodo.view_any',
            
            // Solo puede ver sus materias asignadas
            'materia.view',
            'materia.view_any',
            
            // Solo puede ver los estudiantes de sus materias
            'estudiante.view',
            'estudiante.view_any',
            
            // Solo puede ver y crear logros para sus materias
            'logro.view',
            'logro.view_any',
            'logro.create',
            'logro.update',
            
            // Solo puede asignar logros a estudiantes
            'estudiante_logro.view',
            'estudiante_logro.view_any',
            'estudiante_logro.create',
            'estudiante_logro.update',
        ];

        $profesorRole->givePermissionTo($profesorPermissions);

        // Crear usuario admin por defecto si no existe
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador del Sistema',
                'password' => bcrypt('Password'),
            ]
        );
        $admin->assignRole('admin');

        // Crear profesores de ejemplo con diferentes perfiles
        $profesores = [
            [
                'name' => 'María Elena Rodríguez',
                'email' => 'maria.rodriguez@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Matemáticas y Física',
            ],
            [
                'name' => 'Carlos Alberto Pérez',
                'email' => 'carlos.perez@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Lenguaje y Literatura',
            ],
            [
                'name' => 'Ana Sofía Martínez',
                'email' => 'ana.martinez@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Ciencias Naturales',
            ],
            [
                'name' => 'Luis Fernando González',
                'email' => 'luis.gonzalez@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Ciencias Sociales',
            ],
            [
                'name' => 'Patricia Isabel López',
                'email' => 'patricia.lopez@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Educación Artística',
            ],
            [
                'name' => 'Roberto David Silva',
                'email' => 'roberto.silva@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Educación Física',
            ],
            [
                'name' => 'Carmen Rosa Jiménez',
                'email' => 'carmen.jimenez@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Inglés',
            ],
            [
                'name' => 'Miguel Ángel Torres',
                'email' => 'miguel.torres@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Tecnología e Informática',
            ],
            [
                'name' => 'Laura Beatriz Morales',
                'email' => 'laura.morales@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Ética y Valores',
            ],
            [
                'name' => 'Jorge Andrés Vargas',
                'email' => 'jorge.vargas@liceo.edu.co',
                'password' => bcrypt('Password'),
                'especialidad' => 'Coordinador Académico',
            ]
        ];

        foreach ($profesores as $profesorData) {
            $profesor = User::firstOrCreate(
                ['email' => $profesorData['email']],
                [
                    'name' => $profesorData['name'],
                    'password' => $profesorData['password'],
                ]
            );
            $profesor->assignRole('profesor');
        }

        // Crear un profesor básico adicional (mantener compatibilidad)
        $profesorBasico = User::firstOrCreate(
            ['email' => 'profesor@profesor.com'],
            [
                'name' => 'Profesor General',
                'password' => bcrypt('Password'),
            ]
        );
        $profesorBasico->assignRole('profesor');
    }
}
