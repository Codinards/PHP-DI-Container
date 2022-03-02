# PHP Dependency Injection Container

[![Build Status](https://travis-ci.org/JeanNguimfack/PHP-DI-Container.svg?branch=master)](https://travis-ci.org/JeanNguimfack/PHP-DI-Container)
[![Coverage Status](https://coveralls.io/repos/github/JeanNguimfack/PHP-DI-Container/badge.svg?branch=master)](https://coveralls.io/github/JeanNguimfack/PHP-DI-Container?branch=master)

A simple dependency injection container for php projects

## Installation

```php
composer require njeaner/di-container
```

## How to use it

### Container Initialization

#### Initialize without parameter

```php
$container = new \NJContainer\Container\Container();
```

#### Initialize using the parameter

The container can be constructed with:

- @param **\NJContainer\Container\InstanceDefinition|null** $instance

and **NJContainer\Container\InstanceDefinition** can be constructed with:

- @param **\NJContainer\Container\ContainerDefinition|null**

```php
$containerDefinition = new \NJContainer\Container\ContainerDefinition();

$instance = new \NJContainer\Container\InstanceDefinition();
// or
$instance = new \NJContainer\Container\InstanceDefinition($containerDefinition);


$container = new \NJContainer\Container\Container($instance);
```

### Setting a dependency in the container

Setting one definition

```php
/**
 * @param id string
 * @param mixed $definition
 * @param bool $shared, if "true" container will save this definition instance as a factory dependency
*/
$container->set($id, $definition)
$container->set($id, $definition, true)
```

Setting several definition using an array of definitions

```php
/**
 * @param <string, array> $definitions
 * @example [
 *      'name'=> ['John Doe'],
 *      'stdClass1'  => [new stdClass()],
 *      \Namespace\Route::class => [new \Namespace\Route(), true]
 * ];
 * The true value as second item in dependency array means that you are storing an= factory dependency
*/
$container->add($definitions)
```

Setting several definition using an file with return an array of definitions

```php
/**
 * @param string $definitionsPath, path directory of definition file
*/
$container->addDefinition($definitionsPath)
```

### Setting dependency parameters

Setting a parameter of an alias

```php
/**
 * @param string $id definition id
 * @param string $name parameter name
 * @param mixed $parameter param value to set in definition
*/
$container->setParameter($id, $name, $parameter)
```

Setting several parameter of an alias

```php
/**
 * @param string $id definition id
 * @param array $parameters
*/
$container->setParameters($id, $parameters)
```

### Getting a dependency from the container

```php
/**
 * @param string $id
 * @param bool $shared, if "true" container with retrieve a factory dependency
*/
$container->get($id)
$container->get($id, true)
```

## Use example

```php
class Foo{

}

class Bar {
    public function __construct(Foo $foo, $type = 'Bar'){
        $this->foo = $foo;
        $this->type = $type
    }
}

class FooBar{
    public function __construct(Foo $foo , string $name, array $params){

    }
}
```

```php
    # config.php
    # dependency configuration file

    <?php
    return [
        'name'=> 'John Doe',
        FooBar::class => get()->setParameters(
            'name' => get('name'),
            'params' => ['type' => FooBar::class]
        )
 ];
```

```php
$container = new \NJContainer\Container\Container();

$container->addDefinition('config.php');
$arrayDefinition = [
    FooBar::class => get()->setParameters(
        'params' => add(['bool' => false])
    )
 ];
 
$container->add($arrayDefintion);
$container->lock();

$fooObject = $container->get(Foo::class);
$barObject = $container->get(Foo::class);
// Foo::class and Bar::class has been resolved using autowiring

$foobar = $container->get(FooBar::class);
```
