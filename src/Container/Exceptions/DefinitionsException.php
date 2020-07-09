<?php

namespace NJContainer\Container\Exceptions;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class DefinitionsException extends Exception implements NotFoundExceptionInterface
{
}
