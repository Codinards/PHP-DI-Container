<?php

namespace NJContainer\Container\Contracts;

use Psr\Container\ContainerInterface as ContainerContainerInterface;

/**
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 * @codeCoverageIgnore
 */
interface ContainerInterface extends ContainerContainerInterface
{
    /**
     * Retrieve an instance from the property list of objects.
     *
     * @return mixed
     */
    public function get($id);

    /**
     * Verify if the $id key exists in property list of objects.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;


    public function set(string $id, $definition, bool $shared = false);
}
