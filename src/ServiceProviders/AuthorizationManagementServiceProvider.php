<?php

namespace AuthorizationManagement\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use AuthorizationManagement\Commands\CRUDPolicyMakeCommand;
use AuthorizationManagement\Commands\IndependentGateMakeCommand;

class AuthorizationManagementServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes(
            [__DIR__ . "/../../config/authorization-management-config.php" => config_path("authorization-management-config.php") ] ,
            'authorization-management-config'
        );

        $this->publishes(
            [ __DIR__ . "/../stubs/authorization-management-stubs" => app_path("/stubs/authorization-management-stubs") ] ,
            'authorization-management-stubs'
        );

        if ($this->app->runningInConsole())
        {
            $this->commands( [ CRUDPolicyMakeCommand::class , IndependentGateMakeCommand::class ] );
        }

    }

}
