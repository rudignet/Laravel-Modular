<?php

namespace Lucid\Modular\commands;

use Illuminate\Console\Command;
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
        $moduleName = $this->argument('name');
        $path = config('modules.path')."$moduleName/";
        if(file_exists($path)) {
            $this->error('Module name ' . $moduleName . ' already exists');
            return false;
        }
		
		File::copyDirectory(__DIR__.'../moduleTemplate/',$path); //Create module dir
		
		$this->comment("$moduleName was correctly created, use 'php artisan modules:enable $moduleName' to activate your module");
    }

}