<?php

namespace NJContainer\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

/**
 * @codeCoverageIgnore
 */
class RecursiveException extends Exception implements ContainerExceptionInterface
{
}
