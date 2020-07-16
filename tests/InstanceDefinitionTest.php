<?php

namespace NJContainer\Tests;

use NJContainer\Container\Container;
use NJContainer\Container\ContainerDefinition;
use function NJContainer\Container\get;
use NJContainer\Container\InstanceDefinition;
use NJContainer\Container\RegisterDefinition;

use NJContainer\Tests\TestsClasses\FirstTestClass;
use NJContainer\Tests\TestsClasses\FourthTestClass;
use PHPUnit\Framework\TestCase;

class InstanceDefinitionTest extends TestCase
{
    public function testGetContainer()
    {
        $instanceDef = $this->getInstance();
        $this->assertInstanceOf(ContainerDefinition::class, $instanceDef->getContainer());
    }

    public function testManageParametersFunctions()
    {
        $definition = new ContainerDefinition();
        //Test InstanceDefinition::setParameter() InstanceDefinition::getParameter() InstanceDefinition::hasParameter()
        $instanceDef = $this->getInstance($definition);
        $instanceDef->setParameter(FourthTestClass::class, 'undefine1', get(FirstTestClass::class));
        $container = new Container($instanceDef);
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');

        $this->asserttrue($definition->hasParameter(FourthTestClass::class));
        $this->assertTrue($instanceDef->hasParameter(FourthTestClass::class, 'undefine1'));
        $this->assertInstanceOf(
            RegisterDefinition::class,
            $instanceDef->getParameter(FourthTestClass::class, 'undefine1')
        );

        // Test InstanceDefinition::setParameters() and InstanceDefinition::getParameters()
        $instanceDef = $this->getInstance();
        $instanceDef->setParameters(
            FourthTestClass::class,
            [
                'undefine1' => get(FirstTestClass::class),
                'undefine2' => get(SecondTestClass::class)
            ]
        );
        $container = new Container($instanceDef);
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $parameters = $instanceDef->getParameters(FourthTestClass::class);

        $this->assertIsArray($parameters);
        $this->assertInstanceOf(RegisterDefinition::class, $parameters['undefine1']);
        $this->assertInstanceOf(RegisterDefinition::class, $parameters['undefine2']);
        

        /* Test InstanceDefinition::addShared() and InstanceDefinition::isShared() */
        $this->assertFalse($instanceDef->isShared(FourthTestClass::class));
        $instanceDef->addShared(FourthTestClass::class);
        $this->assertTrue($instanceDef->isShared(FourthTestClass::class));
    }

    public function testForPrivateFunction()
    {
        $instanceDef = $this->getInstance();
        $instanceDef->setParameter(FourthTestClass::class, 'undefine1', get(FirstTestClass::class));
        $container = new Container($instanceDef);
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');

        $this->assertInstanceOf(
            RegisterDefinition::class,
            $instanceDef->getParameter(FourthTestClass::class, 'undefine1')
        );
    }

    private function getInstance(?ContainerDefinition $definition = null): InstanceDefinition
    {
        return new InstanceDefinition($definition);
    }
}
