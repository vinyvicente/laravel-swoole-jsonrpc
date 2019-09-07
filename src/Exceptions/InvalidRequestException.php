<?php

namespace VinyVicente\JsonRpc\Exceptions;

class InvalidRequestException extends ResponseException
{
    /**
     * InvalidRequestException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "Invalid Request", $code = -32600)
    {
        parent::__construct($message, $code);
    }
}
