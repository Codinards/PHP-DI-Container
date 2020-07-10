<?php

namespace NJContainer\Container\Contracts;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 */
interface DefinitionsInterface
{
    /**
     * Retrieve an instance from the property list of objects.
     *
     * @return mixed
     */
    public function get(string $name);

    /**
     * Verify if the $name key exists in property list of objects.
     */
    public function has(string $name): bool;

    /**
     * Add a new object definition in property list of objects.
     *
     * @param mixed $value
     */
    public function add(string $name, $value): self;
}
