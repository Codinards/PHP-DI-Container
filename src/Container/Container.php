<?php

namespace NJContainer\Container;

use NJContainer\Container\Contracts\ContainerInterface;
use NJContainer\Container\Contracts\InstanceDefinitionInterface;
use NJContainer\Container\Exceptions\ContainerException;
use NJContainer\Container\Exceptions\DefinitionsException;
use NJContainer\Tests\TestsClasses\Response;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 *
 * @version 1.1.0
 */
class Container implements ContainerInterface
{
    /**
     * @var InstanceDefinition
     */
    private $instance;

    /**
     * Lock the container after add all dependencies definition.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * \NJContainer\Container\Container Constructor.
     *
     * @param null|\NJContainer\Container\Contracts\InstanceDefinitionInterface $instance
     */
    public function __construct(?InstanceDefinitionInterface $instance = null)
    {
        $this->instance = $instance ?? new InstanceDefinition();
        $this->instance->set(\NJContainer\Container\Contracts\ContainerInterface::class, $this);
        $this->instance->set(\Psr\Container\ContainerInterface::class, $this);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id     identifier of the entry to look for
     * @param bool   $shared if is true, many get function call will return different instances
     *
     * @throws NotFoundExceptionInterface  no entry was found for **this** identifier
     * @throws ContainerExceptionInterface error while retrieving the entry
     *
     * @return mixed
     */
    public function get($id, $shared = false)
    {
        if ($this)
            if (!$shared && $this->has($id)) {
                return $this->instance->get($id);
            }

        if ($shared) {
            return $this->instance->get($id, $shared);
        }

        return $this->instance->get($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->instance->has($id) ?? false;
    }

    /**
     * Set a new definition in the container.
     *
     * @param string $id
     * @param mixed $definition
     * @param bool  $shared,    use "true" to set an factory definition
     *
     * @return self
     */
    public function set(string $id, $definition, bool $shared = false): self
    {
        $this->isNotLocked();
        $this->instance->set($id, $definition, $shared);
        return $this;
    }

    /**
     * Set definitions in the container using an array of [ $id => [$definition, $shared] ].
     * NOTE : the $shared item can be omitted
     *
     * @param array $definitions
     *
     * @return self
     */
    public function add(array $definitions): self
    {
        foreach ($definitions as $id => $definition) {
            if (!\is_array($definition)) {
                $message = 'The value of alias dependency must be an array([$id => [$definition, $shared])';
                throw new DefinitionsException($message);
            }
            $shared = $definition[1] ?? false;
            $this->set($id, $definition[0], $shared);
        }

        return $this;
    }

    /**
     * Set several parameters for specific alias definition.
     * @param string $id
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(string $id, array $parameters): self
    {
        $this->isNotLocked();
        $this->instance->setParameters($id, $parameters);

        return $this;
    }

    /**
     * Set several defintions using a file which return an array of definitions.
     *
     * @param string $definitionsPath
     *
     * @return self
     */
    public function addDefinition(string $definitionsPath): self
    {
        $this->isNotLocked();
        if (!file_exists($definitionsPath)) {
            throw new DefinitionsException('The argument of method "' . __METHOD__ . '" must be a file valid path');
        }

        $definitions = require $definitionsPath;
        if (!\is_array($definitions)) {
            throw new DefinitionsException("The file $definitionsPath must return an array");
        }

        foreach ($definitions as $id => $definition) {
            if (($definition instanceof RegisterDefinition) && $definition->isAdded()) {
                if ($this->instance->getContainer()->has($id)) {
                    $oldDefinition = $this->instance->getContainer()->get($id);
                    if (\is_array($oldDefinition)) {
                        $definition = array_merge($oldDefinition, [$definition->getValue()]);
                    } else {
                        $definition = array_merge(
                            [$oldDefinition],
                            \is_array($definition->getValue())
                                ? $definition->getValue()
                                : [$definition->getValue()]
                        );
                    }
                }
            }
            $this->set($id, $definition);
            if ($id = Response::class) {
                //print_r($this->instance->getContainer()->get($id)); die();
            }
        }

        return $this;
    }

    /**
     * Set a parameter for specific alias definition.
     *
     * @param string $id,        dependency alias
     * @param string $name,      parameter name
     * @param mixed  $parameter, parameter value
     *
     * @return self
     */
    public function setParameter(string $id, string $name, $parameter): self
    {
        $this->isNotLocked();

        $this->instance->setParameter($id, $name, $parameter);

        return $this;
    }

    /**
     * Lock a container and return it.
     *
     * @return self
     */
    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * Verify if the container is not locked.
     *
     * @throws \NJContainer\Container\Exceptions\ContainerException
     *
     * @return void
     */
    private function isNotLocked()
    {
        if (true === $this->locked) {
            $message = "The container is already locked. It's too late to add some dependency definition";
            throw new ContainerException($message);
        }
    }

    /**
     * Return the key list of definition which is resolving.
     *
     * @return array
     */
    public function getResolvingDefinitions(): array
    {
        return $this->instance->getContainer()->getResolvingId();
    }
}
