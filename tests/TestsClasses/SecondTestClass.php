<?php

namespace NJContainer\Tests\TestsClasses;

use Psr\Container\ContainerInterface;

class SecondTestClass
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get the value of container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
