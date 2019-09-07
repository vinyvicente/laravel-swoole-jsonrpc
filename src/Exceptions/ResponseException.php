<?php

namespace VinyVicente\JsonRpc\Exceptions;

class ResponseException extends JsonRpcException
{
    /**
     * @var array|null
     */
    protected $data;

    /**
     * ResponseException constructor.
     *
     * @param string $message
     * @param int $code
     * @param array|null $data
     */
    public function __construct($message = "Internal error", $code = -32603, array $data = null)
    {
        $this->setData($data);

        parent::__construct($message, $code);
    }

    /**
     * @param array|null $data
     */
    public function setData(array $data = null)
    {
        $this->data = $data;
    }

    /**
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }
}
