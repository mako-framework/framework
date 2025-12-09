<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache\psr16\exceptions;

use mako\cache\exceptions\CacheException;
use Psr\SimpleCache\InvalidArgumentException as SimpleCacheInvalidArgumentException;

/**
 * Invalid argument exception.
 */
class InvalidArgumentException extends CacheException implements SimpleCacheInvalidArgumentException
{

}
