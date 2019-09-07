<?php

namespace VinyVicente\JsonRpc\Server;

interface Transformable
{
    /**
     * Customize the transformation rules.
     *
     * @param mixed $item
     * @return mixed
     */
    public function transform($item);
}
