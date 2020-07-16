<?php

namespace NJContainer\Tests;

use Closure;
use NJContainer\Container\Container;
use NJContainer\Container\ContainerDefinition;
use NJContainer\Container\Exceptions\ContainerException;
use NJContainer\Container\Exceptions\DefinitionsException;
use NJContainer\Container\Exceptions\InvalidArgumentException;
use NJContainer\Container\Exceptions\NotFoundException;
use NJContainer\Container\Exceptions\RecursiveException;
use NJContainer\Container\InstanceDefinition;
use NJContainer\Tests\TestsClasses\EigthTestClass;
use NJContainer\Tests\TestsClasses\FactoryTestClass;
use NJContainer\Tests\TestsClasses\FifthTestClass;
use NJContainer\Tests\TestsClasses\FirstTestClass;
use NJContainer\Tests\TestsClasses\FourthTestClass;
use NJContainer\Tests\TestsClasses\NineTestClass;
use NJContainer\Tests\TestsClasses\NoInstanciableClass;
use NJContainer\Tests\TestsClasses\Request;
use NJContainer\Tests\TestsClasses\Response;
use NJContainer\Tests\TestsClasses\Route;
use NJContainer\Tests\TestsClasses\SecondTestClass;
use NJContainer\Tests\TestsClasses\SeventhTestClass;
use NJContainer\Tests\TestsClasses\SixthTestClass;
use NJContainer\Tests\TestsClasses\ThirdTestClass;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;

use function NJContainer\Container\factory;
use function NJContainer\Container\get;

/**
 * @codeCoverageIgnore
 */
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
        $this->assertIsString('name', $this->getContainer()->get('John Doe', true));
    }

    public function testContainerException()
    {
        $container = $this->getContainer();
        $this->expectException(ContainerException::class);
        $this->assertInstanceOf(EigthTestClass::class, $container->get(EigthTestClass::class));
    }

    public function testInstanceDefinitionGetRecursiveException()
    {
        $container = $this->getContainer();
        $container->set(EigthTestClass::class, get()->setParameter('name', get(NineTestClass::class)));
        $this->expectException(RecursiveException::class);
        $this->expectExceptionMessage(
            'Recursive dependency when resolving "NJContainer\Tests\TestsClasses\EigthTestClass'
        );
        $this->assertInstanceOf(EigthTestClass::class, $container->get(EigthTestClass::class));
    }
 
    public function testSetSeveralDefinitionsUsingArray()
    {
        $definitions = [
            'name'       => ['John Doe'],
            'stdClass1'  => [new stdClass()],
            Route::class => [new Route(), true]
        ];

        $container = $this->getContainer()->add($definitions);
        $this->assertSame('John Doe', $container->get('name'));
        $this->assertInstanceOf(stdClass::class, $container->get('stdClass1'));
        $this->assertInstanceOf(Route::class, $container->get(Route::class));
        $this->assertSame($container->get('stdClass1'), $container->get('stdClass1'));
        $this->assertNotSame($container->get(Route::class), $container->get(Route::class));

        $definitions = ['name' => 'John Doe'];
        $this->expectException(DefinitionsException::class);
        $this->expectExceptionMessage(
            'The value of alias dependency must be an array([$id => [$definition, $shared])'
        );
        $container->add($definitions);
    }
    
    public function testLockContainerAndGetResolvingDefinitions()
    {
        $definitions = [
            'name'       => ['John Doe'],
            'stdClass1'  => [new stdClass()],
            Route::class => [new Route()]
        ];
        $container = $this->getContainer();
        $container->add($definitions);
        $container->lock();
        $resolvingDefinitions = $container->getResolvingDefinitions();
        $this->assertIsArray($resolvingDefinitions);
        $this->assertEmpty($resolvingDefinitions);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage(
            "The container is already locked. It's too late to add some dependency definition"
        );
        $container->set("name", "John Doe");
    }

    public function testSimpleAutowiring()
    {
   
        $container = $this->getContainer();
        $this->assertTrue($container->has(ContainerInterface::class));
        $this->assertInstanceOf(Route::class, $container->get(Route::class, true));
        $this->assertSame($container->get(Route::class), $container->get(Route::class));
        $this->assertNotSame($container->get(Route::class, true), $container->get(Route::class, true));
        $this->assertNotSame($container->get(Route::class), $container->get(Route::class, true));
    }

    public function testAutowiringObjectsWithConstructor()
    {
        $container = $this->getContainer();
        $thirdTestClass = $container->get(ThirdTestClass::class);
        $this->assertInstanceOf(ThirdTestClass::class, $thirdTestClass);
        $this->assertInstanceOf(FirstTestClass::class, $thirdTestClass->getFirstTestClass());
        $this->assertInstanceOf(SecondTestClass::class, $thirdTestClass->getSecondTestClass());
        $this->assertInstanceOf(ContainerInterface::class, $thirdTestClass->getSecondTestClass()->getContainer());
    }

    public function testAutowiringObjectWithSetParameters()
    {
        $container = $this->getContainer();
        $container->setParameter(
            Request::class,
            'callback',
            function () {
                return "Hello World";
            }
        );
        $container->setParameters(Response::class, ["method" => 'GET', 'uri' => "/"]);

        
        /** Request assert part  */
        /** @var Request $request */
        $request = $container->get(Request::class);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Route::class, $request->getRoute());
        $this->assertEquals('/', $request->getUrl());
        $callback = $request->getCallback();
        $this->assertInstanceOf(Closure::class, $callback);
        $this->assertEquals('Hello World', $callback());

        /** Response test part */
        /** @var \NJContainer\Tests\TestsClasses\Response $response */
        $response = $container->get(Response::class);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals("GET", $response->getMethod());
        $this->assertEquals("/", $response->getUri());
        $this->assertNull($response->getBody());
        $this->assertIsArray($response->getServerParams());
        $this->assertEquals($response->getHeaders(), $response->getServerParams());
        $this->assertEmpty($response->getHeaders());
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

    public function testNoInstanciableClass()
    {
        $container = $this->getContainer();
        $container->set(EigthTestClass::class, get(NoInstanciableClass::class));
        $this->expectException(NotFoundException::class);
        $message = 'the method "NJContainer\Container\InstanceDefinition::resolveReflection"';
        $message .= ' can not resolve the key "NJContainer\Tests\TestsClasses\NoInstanciableClass"';
        $this->expectExceptionMessage($message);
        $this->assertInstanceOf(EigthTestClass::class, $container->get(EigthTestClass::class));
    }

    public function testInvalidArgumentException()
    {
        $definition = [
            'name' => 'John Doe',
            get()
        ];
        $container = $this->getContainer();
        $container->set('alias', $definition);
        $this->assertIsArray($definition[0]->getParameters());
        $this->expectException(InvalidArgumentException::class);
        $this->assertIsArray($container->get('alias'));
    }

    public function testRecursiveDefinition()
    {
        $container = $this->getContainer();
        $container->setParameter(Request::class, 'callback', get(Request::class));
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->expectException(RecursiveException::class);
        $this->expectExceptionMessage(
            'Recursive dependency when resolving "NJContainer\Tests\TestsClasses\Request"'
        );
        $container->get(Request::class);
    }

    public function testResolveCallback()
    {
        $container = $this->getContainer();
        $container->setParameter(
            Request::class,
            'callback',
            get(EigthTestClass::class)->setParameter(
                'name',
                function (string $name) {
                    return $name;
                }
            )
        );
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->expectException(DefinitionsException::class);
        $message = 'The argument "$name" pass in a definition closure in';
        $message .= ' "/home/john/sites/MyContainer/tests/ContainerTest.php -> line:236"';
        $message .= ' must be a string name of an instanciable class';
        $this->expectExceptionMessage($message);
        $this->assertInstanceOf(Request::class, $container->get(Request::class));
    }

    public function testSimpleAddDefinitions()
    {
        $container = $this->getContainer();
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->assertInstanceOf(FirstTestClass::class, $container->get(FirstTestClass::class));
        $this->assertInstanceOf(SecondTestClass::class, $container->get(SecondTestClass::class));
        $this->assertInstanceOf(ThirdTestClass::class, $container->get(ThirdTestClass::class));
        $this->assertInstanceOf(Route::class, $container->get(FactoryTestClass::class));
        $sevenTestClass = $container->get(SeventhTestClass::class);
        $this->assertInstanceOf(SeventhTestClass::class, $sevenTestClass);
        $this->assertIsArray($sevenTestClass->getParams());
        $this->assertInstanceOf(Route::class, $sevenTestClass->getParams()[0]);
    }

    public function testAddDefinitionUsingHelpers()
    {
        $container = $this->getContainer();
        $container->setParameter(Request::class, 'callback', function () {
            return 'Hello world';
        });
        $container->set(EigthTestClass::class, get()->setParameter('name', 'John Doe'));
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->assertInstanceOf(FourthTestClass::class, $container->get(FourthTestClass::class));
        $router1 = $container->get(FifthTestClass::class)->getRoute();
        $router2 = $container->get(SixthTestClass::class)->getRoute();
        $eightTestClass = $container->get(EigthTestClass::class);
        $this->assertIsNotArray($container->get(Response::class));
        $this->assertInstanceOf(Response::class, $container->get(Response::class));
        $this->assertInstanceOf(Route::class, $router1);
        $this->assertInstanceOf(Route::class, $router2);
        $this->assertInstanceOf(EigthTestClass::class, $eightTestClass);
        $this->assertSame($router1, $router2);
        $this->assertSame($router1, $router2);
        $this->assertEquals('John Doe', $eightTestClass->getName());
        $this->assertInstanceOf(EigthTestClass::class, $container->get(NineTestClass::class)->getEigthTestClass());
    }

    public function testSetDefinitionsWithHelper()
    {
        $container = $this->getContainer();
        $container->setParameter(Request::class, 'callback', function () {
            return 'Hello world';
        });
        $factory = factory();
        $container->set(SixthTestClass::class, $factory);
        $sixthTestClass = $container->get(SixthTestClass::class);
        $this->assertTrue($factory->isFactory());
        $this->assertInstanceOf(SixthTestClass::class, $sixthTestClass);
        $this->assertNotSame($container->get(SixthTestClass::class), $sixthTestClass);
        $container->addDefinition(__DIR__ . '/definitions/config.php');
        $container->addDefinition(__DIR__ . '/definitions/config2.php');
        $this->assertIsArray($container->get('testAddHelper'));
        $this->assertInstanceOf(Request::class, $container->get('testAddHelper2'));
        $this->assertIsArray($container->get('testAddHelper3'));
        $this->assertIsArray($container->get('testAddHelper4'));
    }

    private function getContainer(): Container
    {
        return new Container();
    }
}
