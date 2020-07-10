<?php

namespace NJContainer\Tests;

use NJContainer\Container\Container;
use NJContainer\Container\Exceptions\DefinitionsException;
use NJContainer\Container\Exceptions\NotFoundException;
use NJContainer\Tests\TestsClasses\FifthTestClass;
use NJContainer\Tests\TestsClasses\FirstTestClass;
use NJContainer\Tests\TestsClasses\FourthTestClass;
use NJContainer\Tests\TestsClasses\Request;
use NJContainer\Tests\TestsClasses\Response;
use NJContainer\Tests\TestsClasses\Route;
use NJContainer\Tests\TestsClasses\SecondTestClass;
use NJContainer\Tests\TestsClasses\SixthTestClass;
use NJContainer\Tests\TestsClasses\ThirdTestClass;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

class ContainerTest extends TestCase
{
    public function testSetDefinition()
    {
        $container = $this->getContainer();
        $this->assertInstanceOf(Container::class, $container->get(ContainerInterface::class));
        $container->set('pdo', new PDO('sqlite::memory', null, null));
        $this->assertInstanceOf(PDO::class, $container->get('pdo'));

        /* Test NotFoundException */
        $this->expectException(NotFoundException::class);
        $this->assertIsString('name', $this->getContainer()->get('hello', true));
    }

    public function testSetSeveralDefinitionsUsingArray()
    {
        $definitions = [
            'name'       => ['hello'],
            'stdClass1'  => [new stdClass()],
            Route::class => [new Route(), true]
        ];

        $container = $this->getContainer()->add($definitions);
        $this->assertSame('hello', $container->get('name'));
        $this->assertInstanceOf(stdClass::class, $container->get('stdClass1'));
        $this->assertInstanceOf(Route::class, $container->get(Route::class));
        $this->assertSame($container->get('stdClass1'), $container->get('stdClass1'));
        $this->assertNotSame($container->get(Route::class), $container->get(Route::class));

        $definitions = ['name' => 'hello'];
        $this->expectException(DefinitionsException::class);
        $this->expectExceptionMessage(
            'The value of alias dependency must be an array([$id => [$definition, $shared])'
        );
        $container->add($definitions);
    }

    public function testSimpleAutowiring()
    {
        $container = $this->getContainer();
        $this->assertInstanceOf(Route::class, $container->get(Route::class, true));
        $this->assertSame($container->get(Route::class), $container->get(Route::class));
        $this->assertNotSame($container->get(Route::class, true), $container->get(Route::class, true));
        $this->assertNotSame($container->get(Route::class), $container->get(Route::class, true));
    }

    public function testAutowiringObjectsWithConstructor()
    {
        $container = $this->getContainer();
        $container->setParameter(Request::class, 'callback', function () {
            return 'Hello world';
        });
        $this->assertInstanceOf(Request::class, $container->get(Request::class));
        $callback = $container->get(Request::class)->getCallback();
        $this->assertSame('Hello world', $callback());
    }

    public function testAddWrongDefinitionPath()
    {
        $container = $this->getContainer();

        $this->expectException(DefinitionsException::class);
        $this->expectExceptionMessage(
            "The argument of method \"NJContainer\Container\Container::addDefinition\" must be a file valid path"
        );
        $container->addDefinition('\fake\file\path');
    }

    public function testRequireDefinitionPathDoesnotReturnArray()
    {
        $container = $this->getContainer();
        $this->expectException(DefinitionsException::class);
        $this->expectExceptionMessage('The file ' . __DIR__ . '/definitions/stringConfig.php must return an array');
        $container->addDefinition(__DIR__ . '/definitions/stringConfig.php');
    }

    public function testSimpleAddDefinitions()
    {
        $container = $this->getContainer();
        $container = new Container();
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->assertInstanceOf(FirstTestClass::class, $container->get(FirstTestClass::class));
        $this->assertInstanceOf(SecondTestClass::class, $container->get(SecondTestClass::class));
        $this->assertInstanceOf(ThirdTestClass::class, $container->get(ThirdTestClass::class));
    }

    public function testAddDefinitionUsingHelpers()
    {
        $container = $this->getContainer();
        $container->setParameter(Request::class, 'callback', function () {
            return 'Hello world';
        });
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->assertInstanceOf(FourthTestClass::class, $container->get(FourthTestClass::class));
        $router1 = $container->get(FifthTestClass::class)->getRoute();
        $router2 = $container->get(SixthTestClass::class)->getRoute();
        $this->assertIsNotArray($container->get(Response::class));
        $this->assertInstanceOf(Response::class, $container->get(Response::class));
        $this->assertInstanceOf(Route::class, $router1);
        $this->assertInstanceOf(Route::class, $router2);
        $this->assertSame($router1, $router2);
        $this->assertSame($router1, $router2);
    }

    private function getContainer(): Container
    {
        return new Container();
    }
}
