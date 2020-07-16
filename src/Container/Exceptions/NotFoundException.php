<?php

namespace NJContainer\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @codeCoverageIgnore
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
