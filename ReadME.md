
# PHP Dependency Injection Container

[![Build Status](https://travis-ci.org/JeanNguimfack/PHP-DI-Container.svg?branch=master)](https://travis-ci.org/JeanNguimfack/PHP-DI-Container)
[![Coverage Status](https://coveralls.io/repos/github/JeanNguimfack/PHP-DI-Container/badge.svg?branch=master)](https://coveralls.io/github/JeanNguimfack/PHP-DI-Container?branch=master)


A simple dependency injection container for php projects

## Installation

```php
composer require njeaner/di-container
```

## How to use it

### Construct the container

#### Construct without parameter

```php
$container = new \NJContainer\Container\Container();
```

#### Construct using the parameter

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
$container->set(string $id, $definition, bool $shared = false)    
```
Setting several definition using an array of definitions

```php
$container->add(array $definitions)
```
Setting several definition using an file with return an array of definitions

```php
$container->addDefinition(string $definitionsPath)
```

### Setting dependency parameters

Setting a parameter of an alias

```php
$container->setParameter(string $id, string $name, $parameter)
```
Setting several parameter of an alias

```php
$container->setParameters(string $id, array $parameters)
```
### Getting a dependency from the container

```php
$container->get($id)

// or

$container->get($id, true) to get an factory dependency
```