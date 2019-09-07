<?php

namespace VinyVicente\JsonRpc\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \VinyVicente\JsonRpc\Routing\Route add(string $method, \Closure | array | string $action)
 * @method static void group(array $attributes, \Closure $callback)
 *
 * @see \VinyVicente\JsonRpc\Routing\Router
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoole.jsonrpc.router';
    }
}
