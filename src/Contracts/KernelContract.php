<?php

/*
 * This file is part of the huang-yi/laravel-swoole-http package.
 *
 * (c) Huang Yi <coodeer@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
