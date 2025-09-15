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
            // Los siguientes seeders están comentados temporalmente
            // GradoSeeder::class,
            // AnioEscolarSeeder::class, 
            // PeriodoSeeder::class,
            // MateriaSeeder::class,
            // DirectorGrupoSeeder::class,
            // EstudianteSeeder::class,
            // LogroSeeder::class,
            // DesempenoMateriaSeeder::class,
            // EstudianteLogroSeeder::class,
        ]);
        
        $this->command->info('✅ Siembra de datos completada exitosamente');
        $this->command->info('');
        $this->command->info('🔑 Credenciales de acceso:');
        $this->command->info('   Admin: admin@admin.com / Password');
        $this->command->info('   Profesores: [nombre.apellido]@liceo.edu.co / Password');
    }
}
