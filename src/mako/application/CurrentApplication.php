<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application;

use RuntimeException;

use function vsprintf;

/**
 * Holds the current application instance.
 */
class CurrentApplication
{
	/**
	 * Current application instance.
	 */
	protected static ?Application $application = null;

	/**
	 * Constructor.
	 */
	final public function __construct()
	{
		throw new RuntimeException(vsprintf('%s can not be instantiated.', [static::class]));
	}

	/**
	 * Sets the current application instance.
	 */
	public static function set(Application $application): Application
	{
		return static::$application = $application;
	}

	/**
	 * Returns the current application instance.
	 */
	public static function get(): ?Application
	{
		return static::$application;
	}
}
