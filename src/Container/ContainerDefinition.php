<?php

namespace NJContainer\Container;

use NJContainer\Container\Contracts\ContainerDefinitionInterface;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 */
class ContainerDefinition implements ContainerDefinitionInterface
{
    /**
     * List of all objects constructor scalar parameters.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * list of all instances.
     *
     * @var array
     */
    private $instances = [];

    /**
     * list of all shared instances.
     *
     * @var array
     */
    private $shared = [];

    /**
     * list of definition alias in resolution
     *
     * @var string[]
     */
    private $resolvingId = [];

    /**
     * Get the value of parameters.
     *
     * @param string|null $id service alias name
     *
     * @return array|mixed
     */
    public function getParameters(?string $id = null)
    {
        return (null !== $id) ? $this->parameters[$id] : $this->parameters;
    }

    /**
     * Set the value of parameters.
     *
     * @param string $id        service alias name
     * @param string $paramName parameter key name
     *
     * @return self
     */
    public function setParameters(string $id, array $parameters): self
    {
        foreach ($parameters as $paramName => $value) {
            $this->setParameter($id, $paramName, $value);
        }

        return $this;
    }

    /**
     * Control if the parametres list has key $id.
     *
     * @param string $id        service alias name
     *
     * @return bool
     */
    public function hasParameter(string $id): bool
    {
        return isset($this->parameters[$id]);
    }

    /**
     * retrieve a value from paramters list using it key.
     *
     * @param string $id        service alias name
     * @param string|null $paramName parameter key name
     *
     * @return mixed
     */
    public function getParameter(string $id, ?string $paramName = null)
    {
        return (null !== $paramName) ? $this->parameters[$id][$paramName] : $this->parameters[$id];
    }

    /**
     * Set a value in parameters list binding by a key.
     *
     * @param string $id
     * @param string $paramName
     * @param mixed $parameter
     *
     * @return self
     */
    public function setParameter(string $id, string $paramName, $parameter): self
    {
        $this->parameters[$id][$paramName] = $parameter;

        return $this;
    }

    /**
     * Get list of all instances.
     *
     * @param string|int $id
     *
     * @return array
     */
    public function get($id)
    {
        return $this->instances[$id];
    }
    

    /**
     * Set a definition of an alias.
     *
     * @param string $id
     * @param mixed $definition
     * @param bool $shared
     *
     * @return self
     */
    public function set(string $id, $definition, bool $shared = false):self
    {
        $this->instances[$id] = $definition;
        if ($shared) {
            $this->addShared($id);
        }

        return $this;
    }


    /**
     * look if a dependency alias is define
     *
     *@param string $id

     * @return bool
     */
    public function has($id)
    {
        return isset($this->instances[$id]);
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
        $this->shared[] = $shared;

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
        return \in_array($definition, $this->shared, true);
    }

    /**
     * Set an alias in the list of resolving alias
     *
     * @param string $id
     *
     * @return self
     */
    public function setResolvingId(string $id): self
    {
        $this->resolvingId[] = $id;

        return $this;
    }

    /**
     * look if an alias definition is resolving
     *
     * @param string $id
     *
     * @return boolean
     */
    public function isResolving(string $id)
    {
        return \in_array($id, $this->resolvingId, true);
    }

    /**
     * Get list of service which is in resolving process.
     *
     * @return string[]
     */
    public function getResolvingId(): array
    {
        return $this->resolvingId;
    }

    /**
     * remove an dependency alias in the resolving list
     *
     * @param string $id
     *
     * @return void
     */
    public function deleteResolvingId(string $id): void
    {
        if ($this->isResolving($id)) {
            $key = array_search($id, $this->resolvingId, true);
            unset($this->resolvingId[$key]);
        }
    }
}
