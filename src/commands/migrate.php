<?php

namespace Panel\Modules\commands;

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
        $this->addOption('down');
        $this->addUsage('modules:migrate ModuleName or modules:migrate ModuleName --down to rollBack');
    }

    /**
     * Execute the console command.
     * @return mixed
     */
    public function handle()
    {
        $moduleName = $this->argument('name');
        $path = __DIR__."/../$moduleName/migrations/";
        if(!is_dir($path)) {
            $this->error('There\'s not any module with name ' . $moduleName . ' or it hasn\'t a migrations folder');
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