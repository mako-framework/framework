<?php

namespace mako\common\traits;

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */
use Closure;
use BadMethodCallException;

/**
 * Extendable trait.
 *
 * @author Yamada Taro
 */
trait ExtendableTrait
{
	/**
	 * Class extensions.
	 *
	 * @var array
	 */
	protected static $_extensions;

	/**
	 * Extends the class.
	 *
	 * @param string   $methodName Method name
	 * @param \Closure $closure    Closure
	 */
	public static function extend(string $methodName, Closure $closure)
	{
		static::$_extensions[$methodName] = $closure;
	}

	/**
	 * Executes class extensions.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $name, array $arguments)
	{
		if(!isset(static::$_extensions[$name]))
		{
			throw new BadMethodCallException(vsprintf('Call to undefined method [ %s::%s() ].', [static::class, $name]));
		}

		return static::$_extensions[$name]->bindTo($this, static::class)(...$arguments);
	}

	/**
	 * Executes class extensions.
	 *
	 * @param  string $name      Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public static function __callStatic(string $name, array $arguments)
	{
		if(!isset(static::$_extensions[$name]))
		{
			throw new BadMethodCallException(vsprintf('Call to undefined method [ %s::%s() ].', [static::class, $name]));
		}

		return static::$_extensions[$name]->bindTo(null, static::class)(...$arguments);
	}
}
