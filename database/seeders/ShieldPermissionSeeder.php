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
                'name' => 'Administrador',
                'password' => bcrypt('Password'),
            ]
        );
        $admin->assignRole('admin');

        // Crear usuario profesor por defecto si no existe
        $profesor = User::firstOrCreate(
            ['email' => 'profesor@profesor.com'],
            [
                'name' => 'Profesor',
                'password' => bcrypt('Password'),
            ]
        );
        $profesor->assignRole('profesor');
    }
}
