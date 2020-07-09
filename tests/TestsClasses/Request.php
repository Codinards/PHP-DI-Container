<?php

namespace NJContainer\Tests\TestsClasses;

class Request
{
    protected $route;

    protected $url;

    protected $callback;

    public function __construct(Route $route, $callback, string $url = '/')
    {
        $this->route = $route;
        $this->callback = $callback;
        $this->url = $url;
    }

    /**
     * Get the value of route.
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get the value of url.
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Get the value of callback.
     */
    public function getCallback()
    {
        return $this->callback;
    }
}
