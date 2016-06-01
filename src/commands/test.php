<?php

namespace Lucid\Modular\commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run tests of a module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('name',InputArgument::OPTIONAL,'Module name','all');
        $this->addArgument('config',InputArgument::OPTIONAL,'Config file or key','modules');
        $this->addUsage('modules:test [opt ModuleName] [opt configFile]');
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $config = $this->argument('config');
        if(empty(config($config.'.path'))) {
            $this->error("Config key {$config}.path doesn't exists, maybe you must specify a config file or key to this command");
            return false;
        }

        $moduleName = $this->argument('name');
        if($moduleName != 'all'){
            $this->testModule($moduleName,$config);
        }else //If not modulename we test all modules
            foreach(\Config::get($config.'._modules') as $moduleName)
                $this->testModule($moduleName,$config);

    }

    public function testModule($moduleName,$config){

        $path = config($config.'.path')."$moduleName/tests/";
        if(!is_dir($path) || !count(\File::allFiles($path))) {
            $this->warn('No tests found for module ' . $moduleName);
            return false;
        }
        $this->info("Testing module $moduleName");
        $process = new Process("phpunit $path");
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type || strpos($buffer,'failure:'))
                $this->error($buffer);
            else
                $this->info($buffer);
        });
    }

}
