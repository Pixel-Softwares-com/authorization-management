<?php

namespace AuthorizationManagement\Commands;

use Illuminate\Console\GeneratorCommand;

class IndependentGateMakeCommand extends GeneratorCommand
{
    protected string $independentStubFolderName = "authorization-management-stubs/independent-gate-stubs";
    protected string $independentStubFolderPath = "";


    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:gate';

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        $stub = trim($stub, '/');
        return file_exists($customPath = $this->laravel->basePath( $stub ))
            ? $customPath
            : __DIR__ . "/../" . $stub;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Policies\IndependentGates';
    }
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Making a custom policy to add crud base action methods';

    protected function setCustomIndependentStubFolderPath() : void
    {
        $this->independentStubFolderPath = $this->resolveStubPath('/stubs/' . $this->independentStubFolderName );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() : string
    {
        return $this->independentStubFolderPath . '/independent-gate.stub';
    }

    /**
     * @return string Overriding the parent unnecessary method
     *  To avoid trying to replace User namespace
     */
    protected function userProviderModel() : string
    {
        return "";
    }


    public function handle()
    {
        $this->setCustomIndependentStubFolderPath();
        return parent::handle();
    }

}