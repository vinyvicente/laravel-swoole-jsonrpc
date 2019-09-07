<?php

namespace VinyVicente\JsonRpc\Contracts;

interface KernelContract
{
    /**
     * @param \VinyVicente\JsonRpc\Server\Request $request
     * @return \VinyVicente\JsonRpc\Server\Response
     */
    public function handle($request);

    /**
     * @param \VinyVicente\JsonRpc\Server\Request $request
     * @param \VinyVicente\JsonRpc\Server\Response $response
     */
    public function terminate($request, $response);
}
