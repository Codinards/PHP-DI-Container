<?php

namespace NJContainer\Container;

use NJContainer\Container\Exceptions\ContainerException;
use NJContainer\Container\Exceptions\DefinitionsException;
use NJContainer\Tests\TestsClasses\Response;
use Psr\Container\ContainerInterface;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 *
 * @version 1.0.0
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
     */
    public function __construct(InstanceDefinition $instance = null)
    {
        $this->instance = $instance ?? new InstanceDefinition();
        $this->instance->add(ContainerInterface::class, $this);
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
     * @return mixed entry
     */
    public function get($id, $shared = false)
    {
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
    public function has($id)
    {
        return $this->instance->has($id) ?? false;
    }

    /**
     * Set a new definition in the container.
     *
     * @param mixed $definition
     * @param bool  $shared,    use "true" to set an factory definition
     */
    public function set(string $id, $definition, bool $shared = false): self
    {
        $this->isNotLocked();
        $this->instance->add($id, $definition);
        if ($shared) {
            $this->instance->addShared($id);
        }

        return $this;
    }

    /**
     * Set definitions in the container using an array of [ $id => [$definition, $shared] ].
     *
     * NOTE : the $shared item can be omitted
     */
    public function add(array $definitions): self
    {
        foreach ($definitions as $id => $definition) {
            if (!\is_array($definition)) {
                throw new DefinitionsException('The value of alias dependency must be an array([$id => [$definition, $shared])');
            }
            $shared = $definition[1] ?? false;
            $this->set($id, $definition[0], $shared);
        }

        return $this;
    }

    /**
     * Set several parameters for specific alias definition.
     */
    public function setParameters(string $id, array $parameters): self
    {
        $this->isNotLocked();
        $this->instance->setParameters($id, $parameters);

        return $this;
    }

    /**
     * Set several defintions using a file which return an array of definitions.
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
     * @return void
     */
    public function setParameter(string $id, string $name, $parameter)
    {
        $this->isNotLocked();

        $this->instance->setParameter($id, $name, $parameter);

        return $this;
    }

    /**
     * Lock a container and return it.
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
    public function isNotLocked()
    {
        if (true === $this->locked) {
            throw new ContainerException("The container is already locked. It's too late to add some dependency definition");
        }
    }

    /**
     * Return the key list of definition which is resolving.
     */
    public function getResolvingDefinitions(): array
    {
        return $this->instance->getContainer()->getResolvingId();
    }
}
