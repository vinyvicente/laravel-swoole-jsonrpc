<?php

namespace VinyVicente\JsonRpc\Client;

class Notification
{
    /**
     * @var string
     */
    protected $jsonrpc = '2.0';

    /**
     * @var string
     */
    protected $method;

    /**
     * @var mixed
     */
    protected $params;

    /**
     * Request constructor.
     *
     * @param string $method
     * @param array|null $params
     */
    public function __construct($method, array $params = null)
    {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        $data = [
            'jsonrpc' => $this->jsonrpc,
            'method' => $this->method,
        ];

        if (is_array($this->params)) {
            $data['params'] = $this->params;
        }

        return json_encode($data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
