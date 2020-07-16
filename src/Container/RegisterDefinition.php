<?php

namespace NJContainer\Container;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 */
class RegisterDefinition
{
    /**
     * defintion alias name.
     *
     * @var string
     */
    private $name;

    /**
     * Value for a definition.
     *
     * @var mixed
     */
    private $value;

    /**
     * parameters list for registries instances.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * true when the definition is a factory.
     *
     * @var bool
     */
    private $isFactory = false;

    /**
     * True when the definition is add to an old definition.
     *
     * @var bool
     */
    private $isAdded = false;

    /**
     * Define the class instance as a factory
     *
     * @param string|null $name
     *
     * @return self
     */
    public function factory(?string $name = null): self
    {
        $this->name = $name;
        $this->isFactory = true;

        return $this;
    }

    /**
     * Get parameters list for registries instances.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set parameters list for registries instances.
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Look if the parameters list has key $id.
     *
     * @param string $id service alias name
     *
     * @return bool
     */
    public function hasParameter(string $id): bool
    {
        return isset($this->parameters[$id]);
    }

    /**
     * retrieve a value from parameters list using it key.
     *
     * @param string $id service alias name
     *
     * @return mixed
     */
    public function getParameter(string $id)
    {
        return $this->parameters[$id];
    }

    /**
     * Set a value in parameters list binding by a key.
     *
     * @param string $id
     * @param mixed $parameter
     *
     * @return self
     */
    public function setParameter(string $id, $parameter): self
    {
        $this->parameters[$id] = $parameter;

        return $this;
    }

    /**
     * Get defintion alias name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set definition alias name.
     *
     * @param string|null $name defintion alias name
     *
     * @return self
     */
    public function setName(?string $name = null):self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * retrieve value of isFactory property.
     *
     * @return bool
     */
    public function isFactory():bool
    {
        return $this->isFactory;
    }

    /**
     * Set value of isFactory property
     *
     * @param bool $isFactory Undocumented variable
     *
     * @return self
     */
    public function setIsFactory(bool $isFactory):self
    {
        $this->isFactory = $isFactory;

        return $this;
    }

    /**
     * Set that the definition is add to an old definition.
     *
     * @param bool $isAdded
     *
     * @return self
     */
    public function setIsAdded(bool $isAdded)
    {
        $this->isAdded = $isAdded;

        return $this;
    }

    /**
     * Return true if the definition is add to an old definition.
     *
     * @return bool
     */
    public function isAdded(): bool
    {
        return true === $this->isAdded;
    }

    /**
     * Get value for a definition.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value for a definition.
     *
     * @param mixed $value
     *
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
