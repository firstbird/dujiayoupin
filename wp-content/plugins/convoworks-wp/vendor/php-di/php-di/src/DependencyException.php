<?php

declare (strict_types=1);
namespace Convoworks\DI;

use Psr\Container\ContainerExceptionInterface;
/**
 * Exception for the Container.
 */
class DependencyException extends \Exception implements ContainerExceptionInterface
{
}
