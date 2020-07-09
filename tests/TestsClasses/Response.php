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
}
