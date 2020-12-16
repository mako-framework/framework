<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\preload;

use mako\syringe\ClassInspector;
use ReflectionClass;

use function array_map;
use function array_unique;
use function sort;
use function sprintf;
use function var_export;

/**
 * Preloader generator.
 */
class PreloaderGenerator
{
	/**
	 * Preloader template.
	 *
	 * @var string
	 */
	protected $template = <<<'EOF'
	<?php

	$files = %s;

	foreach($files as $file)
	{
		opcache_compile_file($file);
	}

	EOF;

	/**
	 * Adds missing user defined dependencies to the class array.
	 *
	 * @param  array $classes An array of class names
	 * @return array
	 */
	protected function addMissingDependencies(array $classes): array
	{
		$merged = [];

		foreach($classes as $class)
		{
			$merged[] = $class;

			foreach(ClassInspector::getParents($class) as $parent)
			{
				if((new ReflectionClass($parent))->isUserDefined())
				{
					$merged[] = $parent;
				}
			}

			foreach(ClassInspector::getInterfaces($class) as $interface)
			{
				if((new ReflectionClass($interface))->isUserDefined())
				{
					$merged[] = $interface;
				}
			}

			foreach(ClassInspector::getTraits($class) as $trait)
			{
				if((new ReflectionClass($trait))->isUserDefined())
				{
					$merged[] = $trait;
				}
			}
		}

		return array_unique($merged);
	}

	/**
	 * Returns an array containing the file paths of the provided classes.
	 *
	 * @param  array $classes An array of class names
	 * @return array
	 */
	protected function getClassFilePaths(array $classes): array
	{
		return array_map(function($class)
		{
			return (new ReflectionClass($class))->getFileName();
		}, $classes);
	}

	/**
	 * Generates a preloader.
	 *
	 * @param  array  $classes An array of class names
	 * @return string
	 */
	public function generatePreloader(array $classes): string
	{
		$classes = $this->getClassFilePaths($this->addMissingDependencies($classes));

		sort($classes);

		return sprintf($this->template, var_export($classes, true));
	}
}
