<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Grado;
use App\Models\Periodo;
use App\Models\Materia;
use App\Models\User;
use App\Models\Logro;
use App\Models\EstudianteLogro;
use App\Policies\GradoPolicy;
use App\Policies\PeriodoPolicy;
use App\Policies\MateriaPolicy;
use App\Policies\UserPolicy;
use App\Policies\LogroPolicy;
use App\Policies\EstudianteLogroPolicy;
use App\Policies\BoletinPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Grado::class => GradoPolicy::class,
        Periodo::class => PeriodoPolicy::class,
        Materia::class => MateriaPolicy::class,
        User::class => UserPolicy::class,
        Logro::class => LogroPolicy::class,
        EstudianteLogro::class => EstudianteLogroPolicy::class,
        Estudiante::class => BoletinPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 