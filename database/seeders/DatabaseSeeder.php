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
        $this->call([
            //GradoSeeder::class,
            //PeriodoSeeder::class,
            //MateriaSeeder::class,
            //EstudianteSeeder::class,
            //LogroSeeder::class,
            //EstudianteLogroSeeder::class,
            ShieldPermissionSeeder::class,
        ]);
    }
}
