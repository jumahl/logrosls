<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Grado;
use App\Models\Periodo;
use App\Models\Materia;
use App\Models\User;
use App\Policies\GradoPolicy;
use App\Policies\PeriodoPolicy;
use App\Policies\MateriaPolicy;
use App\Policies\UserPolicy;

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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 