<?php

use \Lucid\Modular\ModulesManager;

//Test route for test purposing
Route::group(['middleware' => ['web'], 'as'=>'{MODULE_NAME}::', 'namespace' => app()->getNamespace().'{MODULE_NAMESPACE}'], function() {
    Route::get('modules/{MODULE_NAME}',function(){
        return view('{MODULE_NAME}::welcome',['foo' => config('{MODULE_NAME}.foo')]);
    });
});


ModulesManager::attachHook('{MODULE_NAME}.attachPoint1','{MODULE_NAME}::testHook', function(){ return 'TEST-HOOK'; });