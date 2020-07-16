<?php
namespace NJContainer\Tests\TestsClasses;

use Psr\Container\ContainerInterface;

class FactoryTestClass
{
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(Route::class);
    }
}
