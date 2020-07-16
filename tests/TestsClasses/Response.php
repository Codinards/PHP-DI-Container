<?php

namespace NJContainer\Tests\TestsClasses;

class Response
{
    private $method;

    private $uri;

    private $headers = [];

    private $body;

    private $serverParams = [];

    public function __construct($method, $uri, array $headers = [], $body = null, array $serverParams = [])
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
        $this->serverParams = $serverParams;
    }

    /**
     * Get the value of method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get the value of uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get the value of headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the value of body
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the value of serverParams
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }
}
