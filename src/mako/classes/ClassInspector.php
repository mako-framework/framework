<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\classes;

use ReflectionAttribute;
use ReflectionClass;

use function array_map;
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
	 * Returns an array of all the attributes of the class.
	 */
	public static function getAttributes(object|string $class): array
	{
		return array_map(
			static fn (ReflectionAttribute $reflection) => $reflection->getName(),
			(new ReflectionClass($class))->getAttributes()
		);
	}

	/**
	 * Returns an array of all the parent classes of the class.
	 */
	public static function getParents(object|string $class, bool $autoload = true): array
	{
		return class_parents($class, $autoload);
	}

	/**
	 * Returns an array of all the interfaces that the class implements.
	 */
	public static function getInterfaces(object|string $class, bool $autoload = true): array
	{
		return class_implements($class, $autoload);
	}

	/**
	 * Returns an array of all traits used by the class.
	 */
	public static function getTraits(object|string $class, bool $autoload = true): array
	{
		// Fetch all traits used by a class and its parents

		$traits = [];

		do {
			$traits += class_uses($class, $autoload);
		}
		while ($class = get_parent_class($class));

		// Find all traits used by the traits

		$search = $traits;

		$searched = [];

		while (!empty($search)) {
			$trait = array_pop($search);

			if (isset($searched[$trait])) {
				continue;
			}

			$traits += $search += class_uses($trait, $autoload);

			$searched[$trait] = $trait;
		}

		// Return complete list of traits used by the class

		return $traits;
	}
}
