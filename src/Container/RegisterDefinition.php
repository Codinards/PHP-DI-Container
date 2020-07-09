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
     * Value for add definition.
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

    public function factory(?string $name = null): self
    {
        $this->name = $name;
        $this->isFactory = true;

        return $this;
    }

    /**
     * Get parameters list for registries instances.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set parameters list for registries instances.
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Control if the parametres list has key $id.
     *
     * @param string $id service alias name
     */
    public function hasParameter(string $id): bool
    {
        return isset($this->parameters[$id]);
    }

    /**
     * retrieve a value from paramters list using it key.
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
     * @param mixed $parameter
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
     * @param string $name defintion alias name
     *
     * @return self
     */
    public function setName(?string $name = null)
    {
        $this->name = $name;
        //$this->isFactory = false;

        return $this;
    }

    /**
     * Get undocumented variable.
     *
     * @return bool
     */
    public function isFactory()
    {
        return $this->isFactory;
    }

    /**
     * Set undocumented variable.
     *
     * @param bool $isFactory Undocumented variable
     *
     * @return self
     */
    public function setIsFactory(bool $isFactory)
    {
        $this->isFactory = $isFactory;

        return $this;
    }

    /**
     * Set that the definition is add to an old definition.
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
     */
    public function isAdded(): bool
    {
        return true === $this->isAdded;
    }

    /**
     * Get value for add definition.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value for add definition.
     *
     * @param mixed $value
     */
    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
