<?php

namespace App\Container;

use NJContainer\Container\RegisterDefinition;

/*
 * @author Jean Nguimfack <nguimjeaner@gmail.com>
 *
 * Helper function which can be used to build dependency definitions in a defintion file
 */

if (!\function_exists("App\Container\get")) {
    /**
     * return a RegistrationDefinition instance which will be use to resolve
     *  a singleton dependency definition.
     */
    function get(?string $alias = null): RegisterDefinition
    {
        return (new RegisterDefinition())->setName($alias)->setIsFactory(false);
    }
}

if (!\function_exists("App\Container\factory")) {
    /**
     * return a RegistrationDefinition instance which will be use to resolve
     *  a factory dependency definition.
     */
    function factory(?string $alias = null): RegisterDefinition
    {
        return (new RegisterDefinition())->factory($alias);
    }
}

if (!\function_exists("App\Container\add")) {
    /**
     * return a RegistrationDefinition instance which will be use to add
     *    a new dependency defintion in an existing dependency definition.
     *
     * If a dependency definition alias does not exist, the function work like
     *  NJContainer\Container\get()
     *
     * @param string|null $alias
     */
    function add($value): RegisterDefinition
    {
        return (new RegisterDefinition())->setIsAdded(true)->setValue($value)->setIsFactory(false);
    }
}
