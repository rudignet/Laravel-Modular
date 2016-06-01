<?php

namespace Lucid\Modular\commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create module database migrations';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->addArgument('name',InputArgument::REQUIRED,'Module name');
        $this->addArgument('config',InputArgument::OPTIONAL,'Config file or key','modules');
        $this->addOption('down');
        $this->addUsage('modules:migrate ModuleName [opt configFile] or modules:migrate ModuleName [opt configFile] --down to rollBack');
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $moduleName = $this->argument('name');
        $config = $this->argument('config');
        if(empty(config($config.'.path'))) {
            $this->error("Config key {$config}.path doesn't exists, maybe you must specify a config file or key to this command");
            return false;
        }

        $path = config($config.'.path')."$moduleName/migrations/";
        if(!is_dir($path)) {
            $this->error("There's not any module with name $moduleName or it hasn\'t a migrations folder");
            return false;
        }

        $migrator = app('migrator');

        if(!$this->option('down'))
            $migrator->run($path);
        else{ //Down tables
            $files = $migrator->getMigrationFiles($path);
            $repository = $migrator->getRepository();
            $migrator->requireFiles($path, $files);
            $ranMigrations = $repository->getRan();
            foreach($files as $file) {
                if(!in_array($file,$ranMigrations))
                    continue;
                $instance = $migrator->resolve($file);
                $instance->down();
                $repository->delete((object)['migration' => $file]);
                $this->comment("<info>Rolled back:</info> $file");
            }
        }

        foreach($migrator->getNotes() as $note)
            $this->comment($note);
    }

}
