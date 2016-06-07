<?php

namespace Lucid\Modular;

class ModulesServiceProvider extends  \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([ 
            __DIR__.'/modules.php' => config_path('modules.php'),
            __DIR__.'/migrations' => database_path('/migrations')
            ]);
        
        $modules = config(env('MODULES_CONFIG_FILE', 'modules').'._modules',[]);
        $path = config(env('MODULES_CONFIG_FILE', 'modules').'.path');
        $commands = [];
        $boots = [];

        if(empty($modules))
            error_log('Any module was found in config: '.env('MODULES_CONFIG_FILE', 'modules').'._modules'.' ¿Are you sure your configuration is ok?');

        foreach ($modules as $module) {
            if(is_dir($path.$module.'/views')) {
                $this->loadViewsFrom($path.$module.'/views', $module);
            }
            if(file_exists($path.$module.'/config.php')) {
                $this->mergeConfigFrom($path.$module.'/config.php', $module);
            }
            if(is_dir($path.$module.'/lang')) {
                $this->loadTranslationsFrom($path.$module.'/lang', $module);
            }
            if(file_exists($path.$module.'/boot.php')) {
                $boots[] = $path.$module.'/boot.php';
            }
            $commands = array_merge($commands,config("{$module}._commands",[]));
        }

        //Route for module assets
        \Route::get('modules/{moduleName}/{path}',function($moduleName,$path){
            return ModulesManager::getAsset($moduleName,$path);
        })->where('path', '(.*)');

        $this->commands($commands);

        foreach($boots as $boot)
            include $boot;

    }

    public function register(){

        $this->app->singleton(ModulesManager::class, function ($app) {
            return new ModulesManager();
        });

        $this->commands([
            'Lucid\Modular\commands\migrate',
            'Lucid\Modular\commands\test',
            'Lucid\Modular\commands\newModule',
        ]);
    }
}

