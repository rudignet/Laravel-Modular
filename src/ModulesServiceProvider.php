<?php

namespace Lucid\Modular;

class ModulesServiceProvider extends  \Illuminate\Support\ServiceProvider
{
    public function boot()
    {
        $modules = config('modules._modules',[]);

        foreach ($modules as $module) {
            if(file_exists(__DIR__.'/'.$module.'/boot.php')) {
                include __DIR__.'/'.$module.'/boot.php';
            }
            if(is_dir(__DIR__.'/'.$module.'/views')) {
                $this->loadViewsFrom(__DIR__.'/'.$module.'/views', $module);
            }
            if(file_exists(__DIR__.'/'.$module.'/config.php')) {
                $this->mergeConfigFrom(__DIR__.'/'.$module.'/config.php', $module);
            }
            if(is_dir(__DIR__.'/'.$module.'/lang')) {
                $this->loadTranslationsFrom(__DIR__.'/'.$module.'/lang', $module);
            }
        }

        //Route for module assets
        \Route::get('modules/{moduleName}/{path}',function($moduleName,$path){
            return ModulesManager::getAsset($moduleName,$path);
        })->where('path', '(.*)');
    }

    public function register(){
        $this->app->singleton(ModulesManager::class, function ($app) {
            return new ModulesManager();
        });

        $this->commands([
            'Panel\Modules\commands\migrate',
            'Panel\Modules\commands\test'
        ]);
    }
}

