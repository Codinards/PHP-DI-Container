<?php

namespace NJContainer\Container\Exceptions;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class RecursiveException extends Exception implements ContainerExceptionInterface
{
}
