<?php

namespace App\Providers;

use App\Models\GroupGate;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            // return in_array($user->email, [
            //     //
            // ]);
            return true;
        });
        if (Schema::hasTable('group_gates')) {
            $group_gates = GroupGate::where('is_deleted', false)->get();
            foreach ($group_gates as $group_gate) {
                $key = $group_gate->key;
                Gate::define($key, function (User $user) use ($key) {
                    $group = $user->group()->first();
                    $count_gates = $group->gates()->where('key', $key)->count();
                    return $count_gates > 0;
                });
            }
        }
    }
}
