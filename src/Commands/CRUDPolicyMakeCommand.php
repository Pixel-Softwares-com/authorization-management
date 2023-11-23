<?php

namespace AuthorizationManagement\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Foundation\Console\PolicyMakeCommand;
use Illuminate\Support\Str;

class CRUDPolicyMakeCommand extends PolicyMakeCommand
{
    protected string $customPolicyStbFolderName = "authorization-management-stubs/policy-stubs";
    protected string $customPolicyStbFolderPath = "";

    protected string $readingPermissions = "";
    protected string $readingFunStringStub = "reading-fun.stub";

    protected string $creatingPermissions = "";
    protected string $creatingFunStringStub = "creating-fun.stub";

    protected string $editingPermissions = "";
    protected string $editingFunStringStub = "editing-fun.stub";

    protected string $deletingPermissions = "";
    protected string $deletingFunStringStub = "deleting-fun.stub";


    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:crud-policy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Making a custom policy to add crud base action methods';

    protected function setCustomPolicyStbFolderPath() : void
    {
        $this->customPolicyStbFolderPath = $this->resolveStubPath('/stubs/' . $this->customPolicyStbFolderName );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub() : string
    {
        return $this->customPolicyStbFolderPath . '/policy.stub';
    }

    /**
     * @throws FileNotFoundException
     */
    protected function replaceDeletingPermissions(string $classStub) : string
    {
        $funString = "";
        if($this->deletingPermissions)
        {
            $funStringStbPath  = $this->customPolicyStbFolderPath . '/' . $this->deletingFunStringStub ;
            $funString = $this->files->get($funStringStbPath);

            $funString = Str::replace("--deleting-permission--" ,"'"  .  $this->deletingPermissions . "'" , $funString);

        }
        return Str::replace("--deleting-fun--" , $funString , $classStub);
    }

    protected function replaceEditingPermissions(string $classStub) : string
    {
        $funString = "";
        if($this->editingPermissions)
        {
            $funStringStbPath  = $this->customPolicyStbFolderPath . '/' . $this->editingFunStringStub;
            $funString = $this->files->get($funStringStbPath);

            $funString = Str::replace("--editing-permission--" , "'"  . $this->editingPermissions . "'" , $funString);

        }
        return Str::replace("--editing-fun--" , $funString , $classStub);
    }

    protected function replaceCreatingPermissions(string $classStub) : string
    {
        $funString = "";
        if($this->creatingPermissions)
        {
            $funStringStbPath  = $this->customPolicyStbFolderPath . '/' . $this->creatingFunStringStub;
            $funString = $this->files->get($funStringStbPath);

            $funString = Str::replace("--creating-permission--" , "'"  . $this->creatingPermissions . "'" , $funString);

        }
        return Str::replace("--creating-fun--" , $funString , $classStub);
    }

    /**
     * @param string $classStub
     * @return string
     * @throws FileNotFoundException
     */
    protected function replaceReadingPermissions(string $classStub) : string
    {
        $funString = "";
        if($this->readingPermissions)
        {
            $funStringStbPath  = $this->customPolicyStbFolderPath . '/' . $this->readingFunStringStub;
            $funString = $this->files->get($funStringStbPath);

            $funString = Str::replace("--reading-permission--" , "'" . $this->readingPermissions . "'" , $funString);
        }
        return Str::replace("--reading-fun--" , $funString , $classStub);
    }

    /**
     * @param string $classStub
     * @return string
     * @throws FileNotFoundException
     */
    protected function replacePermissionStrings(string $classStub) : string
    {
        $classStub = $this->replaceReadingPermissions($classStub);
        $classStub = $this->replaceCreatingPermissions($classStub);
        $classStub = $this->replaceEditingPermissions($classStub);
        return $this->replaceDeletingPermissions($classStub);
    }

    /**
     * @return string Overriding the parent unnecessary method
     *  To avoid trying to replace User namespace
     */
    protected function userProviderModel() : string
    {
        return "";
    }
    /**
     * @param $stub
     * @return string Overriding the parent unnecessary method
     *  To avoid trying to replace User namespace
     */
    protected function replaceUserNamespace($stub) : string
    {
        return $stub;
    }

    /**
     * @param $stub
     * @param $model
     * @return string
     * Overriding the parent unnecessary method
     * To avoid trying to replace any model (user model or the model that requires the user to be authorized to perform action on it
     * when a model option is set by calling command ... the used stub doesn't support any model replacing
     * so no need to do any extra logic to try to replace any model
     */
    protected function replaceModel($stub, $model) : string
    {
        return $stub;
    }

    protected function askForDeletingPermissions() : self
    {
        if( $this->confirm("Do you need to check user's deleting permissions ?" ) )
        {
            $this->deletingPermissions = $this->ask("What is the deleting permission string the user must have ?");
        }
        return $this;
    }

    protected function askForEditingPermissions() : self
    {
        if($this->confirm("Do you need to check user's editing permissions ?"  ) )
        {
            $this->editingPermissions = $this->ask("What is the editing permission string the user must have ?");
        }
        return $this;
    }

    protected function askForCreatingPermissions() : self
    {
        if($this->confirm("Do you need to check user's creating permissions ?" ) )
        {
            $this->creatingPermissions = $this->ask("What is the creating permission string the user must have ?");
        }
        return $this;
    }
    protected function askForReadingPermissions() : self
    {
        if($this->confirm("Do you need to check user's reading permissions ?" ) )
        {
            $this->readingPermissions = $this->ask("What is the reading permission string the user must have ?");
        }
        return $this;
    }

    /**
     * @param $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name) : string
    {
        $this->askForReadingPermissions()
            ->askForCreatingPermissions()
            ->askForEditingPermissions()
            ->askForDeletingPermissions();

        $this->setCustomPolicyStbFolderPath();
        return $this->replacePermissionStrings( parent::buildClass($name) );
    }

}
