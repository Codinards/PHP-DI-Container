<?php

namespace NJContainer\Container;

use NJContainer\Container\ContainerDefinition;
use NJContainer\Container\Contracts\ContainerDefinitionInterface;
use NJContainer\Container\Contracts\InstanceDefinitionInterface;
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
class InstanceDefinition implements InstanceDefinitionInterface
{
    /**
     * @var ContainerDefinition
     */
    protected $container;


    /**
     * InstanceDefinition constructor
     *
     * @param ContainerDefinitionInterface|null $container
     */
    public function __construct(?ContainerDefinitionInterface $container = null)
    {
        $this->container = $container ?? new ContainerDefinition();
    }

    /**
    * Retrieve a dependency instance.
    *
    * @param string $name
    * @param bool $shared
    *
    * @return mixed
    * @throws \NJContainer\Container\Exceptions\ContainerException
    */
    public function get($name, bool $shared = false)
    {
        if ($this->container->isResolving($name)) {
            $this->throwCanNotResolveException(__METHOD__, $name, 2);
        }
    
        $shared = $shared ?: $this->container->isShared($name);

        if (!$shared && $this->has($name)) {
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
                    if (count($parameters) === 1) {
                        $paramName = $parameters[0]->getClass()->getName();
                        if ($this->has($paramName)) {
                            $paramInstance = $this->get($paramName);
                            if ($paramInstance instanceof ContainerInterface) {
                                $instance = $instance($paramInstance);
                                $this->container->set($name, $instance);
                            }
                        }
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

    /**
     * Verify if the $name key exists in property list of instances.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->container->has($name);
    }

    /**
     * Set a dependency alias definition.
     *
     * @param string $id
     * @param mixed $definition
     * @param bool $shared
     *
     * @return self
     */
    public function set(string $id, $definition, bool $shared = false): self
    {
        $this->container->set($id, $definition, $shared);

        return $this;
    }

    /**
     * Resolve an RegisterDefinition definition
     *
     * @param string $name
     * @param RegisterDefinition $registerDefinition
     *
     * @return mixed
     */
    private function resolve(string $name, RegisterDefinition $registerDefinition)
    {
        $alias = $registerDefinition->getName();
        if ($registerDefinition->isFactory()) {
            $this->container->addShared($name);
        }
        if ($alias && $alias !== $name && $this->container->isResolving($alias)) {
            $this->throwCanNotResolveException(__METHOD__, $alias, 2);
        }
        if ($alias && $this->has($alias)) {
            $instance = $this->get($alias, $registerDefinition->isFactory());
            if (!$instance instanceof RegisterDefinition) {
                if (\is_array($instance)) {
                    $instance = $this->resolveArray($name, $instance);
                }

                return $instance;
            }
        }
        $name = $alias ?? $name;
        if (!class_exists($name)) {
            $value = $registerDefinition->getValue();
            if ($value and is_array($value)) {
                return $this->resolveArray($name, $value);
            }
        }

        return $this->resolveReflection($name, $registerDefinition);
    }

    /**
     * Resolve an dependency using reflection
     *
     * @param string $name
     * @param RegisterDefinition|null $registerDefinition
     *
     * @return mixed
     */
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

                            if ($this->hasParameter($name, $param->getName())) {
                                $value =  $this->getParameter($name, $param->getName());
                                if ($value instanceof RegisterDefinition) {
                                    return $this->resolve($param->getName(), $value);
                                }
                                return $value;
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
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * Resolve a callable dependency definition
     *
     * @param callable $reflectionFunction
     *
     * @return mixed
     */
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

    /**
     * Resolve an array dependency definition
     *
     * @param string $name
     * @param array $instance
     *
     * @return array
     */
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
     * Look if a specific parameter an alias is defined
     *
     * @param string $id
     * @param string $paramName
     *
     * @return boolean
     */
    public function hasParameter(string $id, string $paramName):bool
    {
        return $this->container->hasParameter($id, $paramName);
    }

    /**
     * Retrieve a specific parameter for an alias
     *
     * @param string $id
     * @param string $paramName
     *
     * @return mixed
     */
    public function getParameter(string $id, string $paramName)
    {
        return $this->container->getParameter($id, $paramName);
    }

    /**
     * Retrieve all parameters of an alias
     *
     * @param string $id
     *
     * @return mixed
     */
    public function getParameters(string $id)
    {
        return $this->container->getParameters($id);
    }

    /**
     * Set a specific parameter definition of an alias
     *
     * @param string $id
     * @param string $paramName
     * @param mixed $parameter
     *
     * @return self
     */
    public function setParameter(string $id, string $paramName, $parameter): self
    {
        $this->container->setParameter($id, $paramName, $parameter);

        return $this;
    }

    /**
     * Set all parameters definitions of an alias
     *
     * @param string $id        service alias name
     * @param string $paramName parameter key name
     *
     * @return self
     */
    public function setParameters(string $id, array $parameters): self
    {
        $this->container->setParameters($id, $parameters);
        return $this;
    }

    /**
     *  add an instance alias name in list of shared instances.
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
     *
     * @param string $definition
     *
     * @return bool
     */
    public function isShared(string $definition): bool
    {
        return $this->container->isShared($definition);
    }

    /**
     * Retrieve \NJContainer\Container\ContainerDefinition of instance property.
     *
     * @return ContainerDefinition
     */
    public function getContainer():ContainerDefinition
    {
        return $this->container;
    }

    /**
     * Throw some exception .
     *
     * @throw \NJContainer\Container\Exceptions\NotFoundException
     */
    private function throwCanNotResolveException(string $method, string $name, $containerException = false)
    {
        if (!$containerException) {
            throw new NotFoundException('the method "' . $method . "\" can not resolve the key \"$name\"");
        }
        if ($containerException === 2) {
            throw new RecursiveException("Recursive dependency when resolving \"$name\"");
        }
        throw new ContainerException('the method "' . $method . "\" can not resolve the key \"$name\"");
    }
}
