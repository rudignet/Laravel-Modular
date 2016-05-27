<?php

namespace Lucid\Modular;

class ModulesServiceProvider extends  \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $this->publishes([ 
            __DIR__.'/modules.php' => config_path('modules.php'),
            __DIR__.'/migrations') => database_path('/migrations')
            ]);
        
        $modules = config('modules._modules',[]);
        $path = config('modules.path');

        foreach ($modules as $module) {
            if(file_exists($path.$module.'/boot.php')) {
                include $path.$module.'/boot.php';
            }
            if(is_dir($path.$module.'/views')) {
                $this->loadViewsFrom($path.$module.'/views', $module);
            }
            if(file_exists($path.$module.'/config.php')) {
                $this->mergeConfigFrom($path.$module.'/config.php', $module);
            }
            if(is_dir($path.$module.'/lang')) {
                $this->loadTranslationsFrom($path.$module.'/lang', $module);
            }
        }

        //Route for module assets
        \Route::get('modules/{moduleName}/{path}',function($moduleName,$path){
            return ModulesManager::getAsset($moduleName,$path);
        })->where('path', '(.*)');
    }

    public function register(){
        $this->mergeConfigFrom(__DIR__.'/modules.php', 'modules'); 

        $this->app->singleton(ModulesManager::class, function ($app) {
            return new ModulesManager();
        });

        $this->commands([
            'Lucid\Modular\commands\migrate',
            'Lucid\Modular\commands\test'
        ]);
    }
}

