<?php

namespace NJContainer\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @codeCoverageIgnore
 */
class DefinitionsException extends Exception implements NotFoundExceptionInterface
{
}
