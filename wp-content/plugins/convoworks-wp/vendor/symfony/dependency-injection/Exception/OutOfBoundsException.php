<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Convoworks\Symfony\Component\DependencyInjection\Exception;

/**
 * Base OutOfBoundsException for Dependency Injection component.
 */
class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
}
