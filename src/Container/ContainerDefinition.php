<?php

namespace NJContainer\Container;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 */
class ContainerDefinition
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
     * list of service which is in resolving process.
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
     * @param string $paramName parameter key name
     */
    public function hasParameter(string $id, string $paramName = null): bool
    {
        if ($paramName) {
            return isset($this->parameters[$id]) && isset($this->parameters[$id][$paramName]);
        }

        return isset($this->parameters[$id]);
    }

    /**
     * retrieve a value from paramters list using it key.
     *
     * @param string $id        service alias name
     * @param string $paramName parameter key name
     *
     * @return mixed
     */
    public function getParameter(string $id, string $paramName = null)
    {
        return (null !== $paramName) ? $this->parameters[$id][$paramName] : $this->parameters[$id];
    }

    /**
     * Set a value in parameters list binding by a key.
     *
     * @param mixed $parameter
     */
    public function setParameter(string $id, string $paramName, $parameter): self
    {
        $this->parameters[$id][$paramName] = $parameter;

        return $this;
    }

    /**
     * Get list of all instances.
     *
     * @return array
     */
    public function get(string $id)
    {
        return $this->instances[$id];
    }

    /**
     * Set list of all instances.
     *
     * @param mixed $defintion list of all instances
     *
     * @return self
     */
    public function set(string $id, $defintion)
    {
        $this->instances[$id] = $defintion;

        return $this;
    }

    /**
     * @return bool
     */
    public function has(string $id)
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
     * Assert if a defintion is shared (if is a factory).
     *
     * @param string $defintion
     */
    public function isShared(string $definition): bool
    {
        return \in_array($definition, $this->shared, true);
    }

    public function setResolvingId(string $id): self
    {
        $this->resolvingId[] = $id;

        return $this;
    }

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

    public function deleteResolvingId(string $id): void
    {
        if ($this->isResolving($id)) {
            $key = array_search($id, $this->resolvingId, true);
            unset($this->resolvingId[$key]);
        }
    }
}
