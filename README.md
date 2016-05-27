# Laravel-Modular
Structure to use laravel as modular system

## To install this app:
1. Include ```"lucidnetworks/modular": "dev-master"``` on your require composer.json
2. Add ```Lucid\Modular\ModulesServiceProvider::class``` to your providers array
3. Run ```php artisan vendor:publish```
4. Run ```php artisan migrate```

#Create New Module:
1. Create your module structure with ```php artisan modules:new ModuleName```
2. Activate your module with ```php artisan modules:activate ModuleName``` (This will add into modules.php the name of the module to be loaded)

##Modules structure
 * boot.php => I'ts loaded when module its instanciated, place here your module routes, hooks, at any thing we enat to load on module boot
 * config.php => Module configuration file, this config will be loadable with ```config('{module_name}.{key}')```
 * views/ => Module views, this views will be loadable with ```view("{module_name}::{view}")``` this views are overloadable
 * lang/ => Module translations, this translations will be loadable with ```trans("{module_name}::{key}")``` (Use one subfolder for each language, as usual)
 * assets/ => Module public files, this files will be into this public url {your_laravel_url}/modules/{moduleName}/{path_inside_assets_folder}
 * migrations/ => Module migrations, see migrations section to know how to do
 * tests/ => Module test, see tests section to know how to do

##Module routing:
Module routes must be declarated on boot.php, remember you must to use complete namespaces on all methods called into router or use the namespace property
To use routes with parameters use route key 'params_closure' => function(){} that receives a closure who returns all params nedded to create the route

Route sample: ``` Route::group(['middleware' => ['web','auth'], 'as'=>'admin::', 'namespace' => 'Modules\Admin\Controllers', 
'params_closure' => function(){ return ['id' => 23]; }], function() { /* route code */ }); ```

##Hooks:
Hooks allow to register funtions that can be called from your app views
To add a hook use ```ModuleManager::attachHook(string Point, string Name, callable Closure)```

**[Parameters]:**
* Point: Attach point where the hook will be assigned
* Name: Internal unique name for the hook, use always {module_name}::{hook_name} as name. Ex MyModule::MyHook
* Closure: This closure must return an string that will be concatenated with all other hooks attached to the same point when ```ModuleManager::getHook({attach_point})``` was called

###Hooks ordenation:
Each time ``` ModuleManager::attachHook ``` was called, hook will be saved into database, that's allow to order hook depending your preferences, if you don't set an order all hooks will be ordered between his insertion into database
Hook Example: 
```ModuleManager::attachHook('admin.actionBar','MyModule::HookAction1', function(){ return 'TEST-HOOK'; });```

##Module public methods registration
This tool allow you to register a method that can be called as a hook, but in this case it can returns any kind of result, that allow you to set any method of your module public
To register a method as public use ``` ModuleManager::registerModuleFunc(string Name, callable Closure); ```

**[Parameters]:**
* Name: Public unique name for the function, use always {module_name}::{function_name} as name. Ex MyModule::MyFunction
* Closure: Function will receive an array of params and return any kind of result, Ex: function($params){ /* some code */ }
	
###Calling a module public function
After register a method on your module you can acces from anywhere using
```ModuleManager::callModuleFunc(string Name, array Params = [], Default = null)``` or ```callModuleFuncOrFail``` with same signature if you want to generate an exception if {method_name} has not been registered

**[Parameters]:**
* Name: Public unique name for the function, use always {module_name}::{function_name} as name. Ex MyModule::MyFunction
* Params: Array of parameters for the method, will be receibed by the closure
* Default: Value will be returned if Name has not been registered

```callModuleFuncOrFail(string Name, array Params = [],string Message = '')``` will generate an exception if Name has not been registered, in this case Message it's the message will be show by the generated exception. Ex 'You must to install x module' 
	
	
	
