<?php

namespace NJContainer;

use NJContainer\Container\RegisterDefinition;

if (!\function_exists("NJContainer\get")) {
    function get(?string $alias = null): RegisterDefinition
    {
        return (new RegisterDefinition())->setName($alias)->setIsFactory(false);
    }
}

if (!\function_exists("NJContainer\factory")) {
    function factory(?string $alias = null): RegisterDefinition
    {
        return (new RegisterDefinition())->factory($alias);
    }
}

if (!\function_exists("NJContainer\add")) {
    function add($value): RegisterDefinition
    {
        return (new RegisterDefinition())->setIsAdded(true)->setValue($value)->setIsFactory(false);
    }
}
