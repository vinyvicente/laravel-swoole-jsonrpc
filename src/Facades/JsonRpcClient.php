<?php

namespace VinyVicente\JsonRpc\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \VinyVicente\JsonRpc\Client\ConnectionManager
 */
class JsonRpcClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'swoole.jsonrpc.client';
    }
}
