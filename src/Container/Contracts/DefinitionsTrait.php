<?php

namespace NJContainer\Container\Contracts;

use NJContainer\Container\ContainerDefinition;
use NJContainer\Container\Exceptions\ContainerException;
use NJContainer\Container\Exceptions\DefinitionsException;
use NJContainer\Container\Exceptions\InvalidArgumentException;
use NJContainer\Container\Exceptions\NotFoundException;
use NJContainer\Container\Exceptions\RecursiveException;
use NJContainer\Container\RegisterDefinition;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 */
trait DefinitionsTrait
{
    /**
     * @var ContainerDefinition
     */
    protected $container;

    public function __construct(?ContainerDefinition $container = null)
    {
        if ($container) {
            $this->container = $container;
        }
        $this->container = new ContainerDefinition();
    }

    /**
     * Resolve an object.
     *
     * @return void
     */
    public function get(string $name, bool $shared = false)
    {
        if ($this->container->isResolving($name)) {
            throw new RecursiveException("Recursive dependency when resolving \"$name\"");
        }
        $shared = $shared ?: $this->container->isShared($name);
        if (!$shared && $this->container->has($name)) {
            $instance = $this->container->get($name);
            if (\is_callable($instance)) {
                $instance = $this->resolveCallback($instance);
            }
            if ($instance instanceof RegisterDefinition) {
                $instance = $this->resolve($name, $instance);
                if (method_exists($instance, '__invoke')) {
                    $refletionClass = new ReflectionClass($instance);
                    $method = $refletionClass->getMethod('__invoke');
                    $parameters = $method->getParameters();

                    if (ContainerInterface::class === $parameters[0]->getClass()->getName()) {
                        $instance = $instance($this->get(ContainerInterface::class));
                        $this->container->set($name, $instance);
                    }
                }
            }
            if (\is_array($instance)) {
                $instance = $this->resolveArray($name, $instance);
            }

            $this->container->deleteResolvingId($name);

            return $instance;
        }

        return $this->resolveReflection($name);
    }

    private function resolve(string $name, RegisterDefinition $registerDefinition)
    {
        $alias = $registerDefinition->getName();
        if ($registerDefinition->isFactory()) {
            $this->container->addShared($name);
        }

        if ($alias && $this->container->has($alias)) {
            $instance = $this->container->get($alias, $registerDefinition->isFactory());
            if (!$instance instanceof RegisterDefinition) {
                if (\is_array($instance)) {
                    $instance = $this->resolveArray($name, $instance);
                }

                return $instance;
            }
        }
        $name = $alias ?? $name;

        return $this->resolveReflection($name, $registerDefinition);
    }

    private function resolveReflection(string $name, ?RegisterDefinition $registerDefinition = null)
    {
        try {
            $refletionClass = new ReflectionClass($name);
        } catch (ReflectionException $e) {
            $this->throwCanNotResolveException(__METHOD__, $name);
        }

        $this->container->setResolvingId($name);
        if ($refletionClass->isInstantiable()) {
            if (null === $refletionClass->getConstructor()) {
                $instance = $refletionClass->newInstance();
            } else {
                $constructorParams = $refletionClass->getConstructor()->getParameters();

                $instance = $refletionClass->newInstanceArgs(
                    array_map(function ($param) use ($name, $registerDefinition) {
                        if ($this->container->has($param->getName())) {
                            return $this->get($param->getName());
                        }
                        try {
                            $value = $param->getDefaultValue();

                            return $value;
                        } catch (ReflectionException $e) {
                            if ($param->getClass()) {
                                $definition = $param->getClass()->getName();
                                $shared = $this->container->isShared($definition);

                                return $this->get($definition, $shared);
                            }

                            if ($this->container->hasParameter($name, $param->getName())) {
                                return $this->container->getParameter($name, $param->getName());
                            }

                            if ($registerDefinition && $registerDefinition->hasParameter($param->getName())) {
                                $value = $registerDefinition->getParameter($param->getName());
                                if ($value instanceof RegisterDefinition) {
                                    return $this->resolve($param->getName(), $value);
                                }
                                if (\is_callable($value)) {
                                    return $this->resolveCallback($value);
                                }

                                return $value;
                            }

                            $this->throwCanNotResolveException(__METHOD__, $param->getName(), true);
                        }
                    }, $constructorParams)
                );
            }

            if (!$this->getContainer()->isShared($name)) {
                $this->container->set($name, $instance);
            }

            $this->container->deleteResolvingId($name);

            return $instance;
        }

        $this->throwCanNotResolveException(__METHOD__, $name);
    }

    private function resolveCallback($reflectionFunction)
    {
        $instance = new ReflectionFunction($reflectionFunction);
        $parameters = $instance->getParameters();

        return $instance->invokeArgs(
            array_map(function ($param) use ($instance) {
                if (!$param->getClass()) {
                    $message = 'The argument "$' . $param->getName() . '" pass in a definition closure in "';
                    $message .= $instance->getFileName() . ' -> line:' . $instance->getStartLine();
                    $message .= '" must be a string name of an instanciable class';

                    throw new DefinitionsException($message);
                }

                return $this->get($param->getClass()->getName());
            }, $parameters)
        );
    }

    private function resolveArray(string $name, array $instance): array
    {
        foreach ($instance as $key => $definition) {
            if (\is_string($definition)) {
                if (class_exists($definition)) {
                    $instance[$key] = $this->get($definition);
                } else {
                    $instance[$key] = $definition;
                }
            } elseif ($definition instanceof RegisterDefinition) {
                $name = (\is_int($key) || $definition->getName()) ? $definition->getName() : $key;
                if (null === $name) {
                    throw new InvalidArgumentException();
                }
                $instance[$key] = $this->resolve($name, $definition);
            } elseif (\is_array($definition)) {
                $instance[$key] = $this->resolveArray($key, $definition);
            } elseif (\is_callable($definition)) {
                $instance[$key] = $this->resolveCallback($definition);
            } else {
                $instance[$key] = $definition;
            }
        }

        return $instance;
    }

    /**
     * @return bool
     */
    private function hasParameter(string $id, string $paramName)
    {
        return $this->container->has($id, $paramName);
    }

    /**
     * @return mixed
     */
    public function getParameter(string $id, string $paramName)
    {
        return $this->container->getParameter($id, $paramName);
    }

    /**
     * @return mixed
     */
    public function getParameters(string $id)
    {
        return $this->container->getParameters($id);
    }

    /**
     * Set a value in parameters list binding by a key.
     *
     * @param mixed $parameter
     */
    public function setParameter(string $id, string $paramName, $parameter): self
    {
        $this->container->setParameter($id, $paramName, $parameter);

        return $this;
    }

    /**
     * Set the value of parameters.
     *
     * @param string $id        service alias name
     * @param string $paramName parameter key name
     */
    public function setParameters(string $id, array $parameters): self
    {
        foreach ($parameters as $paramName => $value) {
            $this->setParameter($id, $paramName, $value);
        }

        return $this;
    }

    /**
     *  add an instance alias name in list of all shared instances.
     *
     * @param string $shared list of all shared instances
     *
     * @return self
     */
    public function addShared(string $shared)
    {
        $this->container->addShared($shared);

        return $this;
    }

    /**
     * Assert if a definition is shared (if is a factory).
     */
    public function isShared(string $definition): bool
    {
        return $this->container->isShared($definition);
    }

    /**
     * Get the value of container.
     *
     * @return ContainerDefinition
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Undocumented function.
     *
     * @throw \NJContainer\Container\Exceptions\NotFoundException
     */
    private function throwCanNotResolveException(string $method, string $name, $containerException = false)
    {
        if (!$containerException) {
            throw new NotFoundException('the method "' . $method . "\" can not resolve the key \"$name\"");
        }
        throw new ContainerException('the method "' . $method . "\" can not resolve the key \"$name\"");
    }
}
