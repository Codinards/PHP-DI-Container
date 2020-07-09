<?php

namespace NJContainer\Tests\TestsClasses;

class FifthTestClass
{
    protected $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Get the value of route.
     */
    public function getRoute()
    {
        return $this->route;
    }
}
