<?php

namespace App\Providers;

use App\Models\Day;
use App\Models\Entity;
use App\Policies\DayPolicy;
use App\Policies\EntityPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Entity::class => EntityPolicy::class,
        Day::class => DayPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
