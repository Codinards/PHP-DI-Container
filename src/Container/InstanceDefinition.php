<?php

namespace NJContainer\Container;

use NJContainer\Container\Contracts\DefinitionsInterface;
use NJContainer\Container\Contracts\DefinitionsTrait;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 */
class InstanceDefinition implements DefinitionsInterface
{
    use DefinitionsTrait;

    /**
     * Verify if the $name key exists in property list of instances.
     */
    public function has(string $name): bool
    {
        return $this->container->has($name);
    }

    /**
     * Add a new object definition in instances list.
     *
     * @param mixed $value
     *
     * @return self
     */
    public function add(string $name, $value): DefinitionsInterface
    {
        $this->container->set($name, $value);

        return $this;
    }
}
