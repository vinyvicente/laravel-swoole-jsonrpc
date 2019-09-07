<?php

namespace VinyVicente\JsonRpc\Exceptions;

class ParseErrorException extends ResponseException
{
    /**
     * ParseErrorException constructor.
     *
     * @param string $message
     * @param int $code
     */
    public function __construct($message = "Parse error", $code = -32700)
    {
        parent::__construct($message, $code);
    }
}
