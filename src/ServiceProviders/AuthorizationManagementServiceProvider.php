<?php

namespace AuthorizationManagement\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use AuthorizationManagement\Commands\CRUDPolicyMakeCommand;
use AuthorizationManagement\Commands\IndependentGateMakeCommand;

class AuthorizationManagementServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Publish main config file
        $this->publishes(
            [__DIR__ . "/../../config/authorization-management-config.php" => config_path("authorization-management-config.php")],
            'authorization-management-config'
        );

        // Publish stubs
        $this->publishes(
            [__DIR__ . "/../stubs/authorization-management-stubs" => base_path("/stubs/authorization-management-stubs")],
            'authorization-management-stubs'
        );

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([CRUDPolicyMakeCommand::class, IndependentGateMakeCommand::class]);
        }
    }

    public function register()
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . "/../../config/authorization-management-config.php",
            'authorization-management-config'
        );
    }
}
