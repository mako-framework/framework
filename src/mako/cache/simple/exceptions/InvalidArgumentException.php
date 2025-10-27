<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\simple\exceptions;

use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;
use RuntimeException;

/**
 * Invalid argument exception.
 */
class InvalidArgumentException extends RuntimeException implements SimpleCacheInvalidArgumentException
{

}
