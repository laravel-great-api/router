<?php

namespace LaravelGreatApi\Router\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelGreatApi\Router\Registrar;

/**
 * Class Route
 *
 * @method static \LaravelGreatApi\Router\PendingServerRegistration server(string $name)
 */
class Route extends Facade
{
    /**
	 * Get facade accessor
	 * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Registrar::class;
    }
}
