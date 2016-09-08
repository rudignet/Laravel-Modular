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
        $serviceProviders = [];

        if(empty($modules))
            error_log('Any module was found in config: '.env('MODULES_CONFIG_FILE', 'modules').'._modules'.' ¿Are you sure your configuration is ok?');

        foreach ($modules as $module) {
            $dir_exp = explode('/',$path);
            $namespace = app()->getNamespace().$dir_exp[count($dir_exp) - 2].'\\'.$module;

            if(is_dir($path.$module.'/views')) {
                $this->loadViewsFrom($path.$module.'/views', $module);
            }
            if(file_exists($path.$module.'/config.php')) {
                $this->mergeConfigFrom($path.$module.'/config.php', $module);
            }
            if(is_dir($path.$module.'/lang')) {
                $this->loadTranslationsFrom($path.$module.'/lang', $module);
            }
            if(file_exists($path.$module.'/ServiceProvider.php')) {
                $serviceProviders[$namespace] = $path.$module.'/ServiceProvider.php';
            }
            $commands = array_merge($commands,config("{$module}._commands",[]));
        }
                
        //Route for module assets
        \Route::get('modules/{moduleName}/{path}',function($moduleName,$path){
            return ModulesManager::getAsset($moduleName,$path);
        })->where('path', '(.*)');

        $this->commands($commands);

        foreach($serviceProviders as $namespace => $serviceProvider){
            $ServiceProviderClass = "$namespace\\ServiceProvider";

            if(method_exists($ServiceProviderClass,'boot'))
                $ServiceProviderClass::boot();
        }

    }

    public function register(){

        $this->app->singleton('lucid-modular', function ($app) {
            return new ModulesManager();
        });

        $this->commands([
            'Lucid\Modular\commands\migrate',
            'Lucid\Modular\commands\test',
            'Lucid\Modular\commands\newModule',
        ]);
    }
}

