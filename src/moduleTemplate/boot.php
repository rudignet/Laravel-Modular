<?php

//Test route for test purposing
Route::group(['middleware' => ['web'], 'as'=>'{MODULE_NAME}::', 'namespace' => app()->getNamespace().'{MODULE_NAMESPACE}'], function() {
    Route::get('modules/{MODULE_NAME}',function(){
        return view('{MODULE_NAME}::welcome',['foo' => config('{MODULE_NAME}.foo')]);
    });
});