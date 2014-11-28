<?php

namespace Kinglozzer\TinyPng\Http;

class Response
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $body;

    /**
     * @param string $body The response body
     * @param int $statusCode The HTTP status code
     */
    public function __construct($body, $statusCode)
    {
        $this->setBody($body);
        $this->setStatusCode($statusCode);
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
