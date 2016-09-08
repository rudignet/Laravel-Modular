<?php namespace Lucid\Modular;

use Illuminate\Support\Facades\Facade;

class ModularFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'lucid-modular';
    }
}
