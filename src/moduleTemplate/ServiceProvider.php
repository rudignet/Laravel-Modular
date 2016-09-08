<?php

namespace MODULE_REAL_NAMESPACE;

use \Lucid\Modular\ModulesManager;
use \Route;

class ServiceProvider
{
    public static function boot()
    {
        // Boot code, routes, hooks, etc
        //Test route for test purposing
        Route::group(['middleware' => ['web'], 'prefix' => 'modules/{MODULE_NAME}', 'as'=>'{MODULE_NAME}::', 'namespace' => __NAMESPACE__], function() {
            Route::get('/',function(){
                return view('{MODULE_NAME}::welcome',['foo' => config('{MODULE_NAME}.foo')]);
            });
        });
        
        ModulesManager::attachHook('{MODULE_NAME}.attachPoint1','{MODULE_NAME}::testHook', function(){ return 'TEST-HOOK'; });
    }

    public static function register(){
        // Register code
    }
}

