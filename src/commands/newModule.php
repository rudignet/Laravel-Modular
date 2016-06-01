<?php

namespace Lucid\Modular\commands;

use Illuminate\Console\Command;
use \File;
use Symfony\Component\Console\Input\InputArgument;

class newModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('name',InputArgument::REQUIRED,'Module name');
        $this->addUsage('modules:new ModuleName');
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $moduleName = ucfirst(camel_case($this->argument('name')));
        $path = config(env('MODULES_CONFIG_FILE', 'modules').'.path')."$moduleName/";

        if(file_exists($path)) {
            $this->error('Module name ' . $moduleName . ' already exists');
            return false;
        }

		$result = File::copyDirectory(__DIR__.'/../moduleTemplate/',$path); //Create module dir

        if($result) {
            //Replacing test template tags
            $dir_exp = explode('/',$path);
            $namespace = app()->getNamespace().'\\\\'.$dir_exp[count($dir_exp) - 3].'\\\\'.$dir_exp[count($dir_exp) - 2];
            File::put($path.'boot.php',str_replace(['{MODULE_NAMESPACE}','{MODULE_NAME}'],[$namespace,$moduleName],File::get($path.'boot.php')));

            $assets_url = "modules/$moduleName/";
            File::put($path.'views/welcome.blade.php',str_replace(['{ASSETS_URL}','{MODULE_NAME}'],[$assets_url,$moduleName],File::get($path.'views/welcome.blade.php')));


            File::put($path.'tests/ExampleTest.php',str_replace(['{MODULE_URL}','{MODULE_NAME}'],[$assets_url,$moduleName],File::get($path.'tests/ExampleTest.php')));

            $this->info("$moduleName was correctly created");
            $this->info("Your module example was placed on this url: {your_laravel_url}/modules/$moduleName , to activate your module add $moduleName to '_modules' array inside modular config file");
        }else
            $this->error('An error was ocurred while ' . $moduleName . ' was created');
    }

}
