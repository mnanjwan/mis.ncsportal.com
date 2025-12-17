<?php

namespace App\Providers;

use App\Models\Emolument;
use App\Models\LeaveApplication;
use App\Models\ManningRequest;
use App\Models\Officer;
use App\Policies\EmolumentPolicy;
use App\Policies\LeaveApplicationPolicy;
use App\Policies\ManningRequestPolicy;
use App\Policies\OfficerPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Officer::class => OfficerPolicy::class,
        Emolument::class => EmolumentPolicy::class,
        LeaveApplication::class => LeaveApplicationPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Officer::class, OfficerPolicy::class);
        Gate::policy(Emolument::class, EmolumentPolicy::class);
        Gate::policy(LeaveApplication::class, LeaveApplicationPolicy::class);
        Gate::policy(ManningRequest::class, ManningRequestPolicy::class);
    }
}
