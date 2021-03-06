<?php

/*
* 2006-2015 Lucid Networks
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
*
* DISCLAIMER
*
*  Date: 9/3/16 17:57
*  @author Networkkings <info@lucidnetworks.es>
*  @copyright  2006-2015 Lucid Networks
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

namespace Lucid\Modular;

use League\Flysystem\Util\MimeType;

class ModulesManager
{
    private static $hooks = [];
    private static $ModulesFunctions = [];
    public static $css = [];
    public static $js = [];

    /**
     * Attach a method to a hook name
     * @param string $attachName Attach name 
     * @param string $hookName Hook internal name as Module::Name
     * @param callable $callable
     */
    public static function attachHook($attachName,$hookName,callable $callable){
        if(empty(self::$hooks[$attachName]))
            self::$hooks[$attachName] = [];

        self::$hooks[$attachName][$hookName][] = $callable;

        try {
            HookPosition::firstOrCreate(['attach_name' => $attachName, 'name' => $hookName]);
        }catch(\PDOException $e){
            error_log('Modular couldn\'t connect to database, hooks order was disabled. Check that you have run Modular migrations');
        }
    }

    /**
     * Get an array of responses from all functions attached to a hook
     * @param $attachName
     * @return array|bool
     */
    public static function getHook($attachName){
        if(empty(self::$hooks[$attachName]))
            return false;

        $result = '';
        try {
            $hooks = HookPosition::where('attach_name', '=', $attachName)->orderBy('order', 'ASC')->get();
        }catch(\PDOException $e){
            $hooks = !empty(self::$hooks[$attachName]) ? self::$hooks[$attachName] : [];
        }

        foreach($hooks as $index => $hook){
            $name = is_object($hook) ? $hook->name : $index;
            if(empty(self::$hooks[$attachName][$name]))
                continue;

            foreach(self::$hooks[$attachName][$name] as $function)
                if(is_callable($function))
                    $result .= (string)$function();
                else
                    $result .= (string)$function;
        }
        return "<!-- Hook $attachName --> ".chr(10).$result.chr(10)."<!-- /Hook $attachName -->";
    }
    
    /**
     * Register a public module function
     * @param string $functionName Function name as Module::FunctionName
     * @param callable $closure Closure($params) will be called, it receive only an array with params
     */
    public static function registerModuleFunc($functionName,callable $closure){
		if(empty(self::$ModulesFunctions[$functionName]))
			self::$ModulesFunctions[$functionName] = $closure;
		else
			throw new \Exception("Function $functionName was already registered!");
    }
    
    /**
     * Execute a module function or fail if function not registered
     * @param string $functionName Function name as Module::FunctionName
     * @param array $params Params to send to closure
	 * @param string $message Message to attach exception is function doesnt's exist
	 * @return mixed
     */
    public static function callModuleFuncOrFail($functionName,array $params = [],$message = null){
		if(!empty(self::$ModulesFunctions[$functionName]))
			return self::$ModulesFunctions[$functionName]($params);
		else
			throw new \Exception("Function $functionName was not registered!".chr(10).$message);
    }
    
    /**
     * Execute a module function or returns $default value
     * @param string $functionName Function name as Module::FunctionName
     * @param array $params Params to send to closure
	 * @param mixed $default Default value if function doesn't registered
	 * @return mixed
     */
    public static function callModuleFunc($functionName,array $params = [],$default = null){
		if(!empty(self::$ModulesFunctions[$functionName]))
			return self::$ModulesFunctions[$functionName]($params);
		else
			return $default;
    }
    
    /**
     * Return a module asset
     * @param $module
     * @param $path
     * @return string
     */
    public static function getAsset($module,$path){
        $path = str_replace(['../','..\\'],'',$path);
        $realPath = config(env('MODULES_CONFIG_FILE', 'modules').'.path')."$module/assets/$path";
        if(!file_exists($realPath))
            abort(404);

        return response()->download($realPath,null,['Content-Type' => MimeType::detectByFilename($realPath)],'inline');
    }

    /**
     * Return all route names that contains :: (modules)
     * @return array
     */
    public static function getRouteNames(){
        $routeNames = [];
        foreach(\Route::getRoutes()->getIterator() as $route) {
            $name  = $route->getName();
            if(!empty($name) && strpos($name,'::')) {
                $routeNames[$name] = $name;
            }
        }
        return $routeNames;
    }

    /**
     * Generate a message that will be show on next reload
     * @param $message
     * @param string $type
     * @param string $title
     * @param bool $dismissible
     */
    public static function displayHeaderMessage($message, $type = 'danger', $title = '', $dismissible = true, $icon = ''){
        $messages = \Session::get('module::messages',[]);
        if(empty($icon))
            switch($type){
                case 'danger':
                    $icon = 'fa-ban';
                    break;
                case 'warning';
                    $icon = 'fa-warning';
                    break;
                case 'info':
                    $icon = 'fa-info';
                    break;
                default:
                    $icon = 'fa-check';
            }

        $messages[] = ['type' => $type, 'title' => $title, 'icon' => $icon, 'message' => $message, 'dismissible' => (bool)$dismissible];
        \Session::set('module::messages',$messages);
    }

    /**
     * Devuelve los mensajes pendientes
     * @param bool $clear
     * @return mixed
     */
    public static function getHeaderMessages($clear = true){
        $messages = \Session::get('module::messages',[]);
        if($clear)
            \Session::forget('module::messages');
        return $messages;
    }
    
}
