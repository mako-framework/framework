<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\common\traits;

use BadMethodCallException;
use Closure;

use function vsprintf;

/**
 * Extendable trait.
 */
trait ExtendableTrait
{
	/**
	 * Class extensions.
	 */
	protected static array $_extensions = [];

	/**
	 * Adds a method to the class.
	 */
	public static function addMethod(string $methodName, Closure $closure): void
	{
		static::$_extensions[$methodName] = $closure;
	}

	/**
	 * Executes class extensions.
	 */
	public function __call(string $name, array $arguments): mixed
	{
		if(!isset(static::$_extensions[$name]))
		{
			throw new BadMethodCallException(vsprintf('Call to undefined method [ %s::%s() ].', [static::class, $name]));
		}

		return static::$_extensions[$name]->bindTo($this, static::class)(...$arguments);
	}

	/**
	 * Executes class extensions.
	 */
	public static function __callStatic(string $name, array $arguments): mixed
	{
		if(!isset(static::$_extensions[$name]))
		{
			throw new BadMethodCallException(vsprintf('Call to undefined method [ %s::%s() ].', [static::class, $name]));
		}

		return static::$_extensions[$name]->bindTo(null, static::class)(...$arguments);
	}
}
