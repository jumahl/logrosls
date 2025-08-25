<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Iniciando la siembra de datos...');
        
        $this->call([
            // 1. Primero crear roles, permisos y usuarios (admin y profesores)
            ShieldPermissionSeeder::class,
            
            // 2. Crear estructura académica básica
            GradoSeeder::class,
            PeriodoSeeder::class,
            
            // 3. Crear materias (requiere usuarios/profesores)
            MateriaSeeder::class,
            
            // 4. Asignar directores de grupo (requiere usuarios y grados)
            DirectorGrupoSeeder::class,
            
            // 5. Crear estudiantes (requiere grados)
            EstudianteSeeder::class,
            
            // 6. Crear logros por materia (requiere materias)
            LogroSeeder::class,
            
            // 7. Crear desempeños de materia (requiere estudiantes, materias, períodos)
            DesempenoMateriaSeeder::class,
            
            // 8. Finalmente crear asignaciones de logros (requiere desempeños)
            EstudianteLogroSeeder::class,
        ]);
        
        $this->command->info('✅ Siembra de datos completada exitosamente');
        $this->command->info('');
        $this->command->info('📊 Datos creados:');
        $this->command->info('   👥 Usuarios: Admin + 10 Profesores');
        $this->command->info('   🎓 Grados: Preescolar a Once (12 grados)');
        $this->command->info('   📅 Períodos: 4 períodos académicos del año actual');
        $this->command->info('   📚 Materias: 16 materias distribuidas por grados');
        $this->command->info('   👨‍🏫 Directores: 10 directores de grupo asignados');
        $this->command->info('   👨‍🎓 Estudiantes: ~60 estudiantes distribuidos en todos los grados');
        $this->command->info('   🎯 Logros: ~100 logros académicos por materias');
        $this->command->info('   � Desempeños: Notas consolidadas por estudiante/materia/período');
        $this->command->info('   ✅ Logros Asignados: Múltiples logros asociados a cada desempeño');
        $this->command->info('');
        $this->command->info('🔑 Credenciales de acceso:');
        $this->command->info('   Admin: admin@admin.com / Password');
        $this->command->info('   Profesores: [nombre.apellido]@liceo.edu.co / Password');
    }
}
