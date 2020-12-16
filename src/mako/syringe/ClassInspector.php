<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe;

use function array_pop;
use function class_implements;
use function class_parents;
use function class_uses;
use function get_parent_class;

/**
 * Class inspector.
 */
class ClassInspector
{
	/**
	 * Returns an array of all the parent classes of the class.
	 *
	 * @param  object|string $class    Class name or class instance
	 * @param  bool          $autoload Should the class be autoloaded?
	 * @return array
	 */
	public static function getParents($class, bool $autoload = true): array
	{
		return class_parents($class, $autoload);
	}

	/**
	 * Returns an array of all the interfaces that the class implements.
	 *
	 * @param  object|string $class    Class name or class instance
	 * @param  bool          $autoload Should the class be autoloaded?
	 * @return array
	 */
	public static function getInterfaces($class, bool $autoload = true): array
	{
		return class_implements($class, $autoload);
	}

	/**
	 * Returns an array of all traits used by the class.
	 *
	 * @param  object|string $class    Class name or class instance
	 * @param  bool          $autoload Should the class be autoloaded?
	 * @return array
	 */
	public static function getTraits($class, bool $autoload = true): array
	{
		// Fetch all traits used by a class and its parents

		$traits = [];

		do
		{
			$traits += class_uses($class, $autoload);
		}
		while($class = get_parent_class($class));

		// Find all traits used by the traits

		$search = $traits;

		$searched = [];

		while(!empty($search))
		{
			$trait = array_pop($search);

			if(isset($searched[$trait]))
			{
				continue;
			}

			$traits += $search += class_uses($trait, $autoload);

			$searched[$trait] = $trait;
		}

		// Return complete list of traits used by the class

		return $traits;
	}
}
