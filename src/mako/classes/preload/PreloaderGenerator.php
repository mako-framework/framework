<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\classes\preload;

use mako\classes\ClassInspector;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_map;
use function array_unique;
use function class_exists;
use function interface_exists;
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
	 * Get class names from reflection type.
	 *
	 * @param  \ReflectionType $type Reflection type
	 * @return array
	 */
	protected function getTypeClasses(ReflectionType $type): array
	{
		$classes = [];

		if($type instanceof ReflectionNamedType)
		{
			$class = $type->getName();

			if(!$type->isBuiltin() && (class_exists($class) || interface_exists($class)) && (new ReflectionClass($class))->isUserDefined())
			{
				$classes[] = $class;
			}
		}
		elseif(PHP_VERSION_ID >= 80100 && $type instanceof ReflectionIntersectionType)
		{
			/** @var \ReflectionNamedType $intersectionType */
			foreach($type->getTypes() as $intersectionType)
			{
				$class = $intersectionType->getName();

				if((class_exists($class) || interface_exists($class)) && (new ReflectionClass($class))->isUserDefined())
				{
					$classes[] = $class;
				}
			}
		}
		elseif($type instanceof ReflectionUnionType)
		{
			/** @var \ReflectionNamedType $unionType */
			foreach($type->getTypes() as $unionType)
			{
				if(PHP_VERSION_ID >= 80200 && $unionType instanceof ReflectionIntersectionType)
				{
					$classes = [...$classes, ...$this->getTypeClasses($unionType)];

					continue;
				}

				$class = $unionType->getName();

				if(!$unionType->isBuiltin() && (class_exists($class) || interface_exists($class)) && (new ReflectionClass($class))->isUserDefined())
				{
					$classes[] = $class;
				}
			}
		}

		return $classes;
	}

	/**
	 * Adds missing user defined dependencies to the class array.
	 *
	 * @param  iterable $classes An iterable of class names
	 * @return array
	 */
	protected function addMissingDependencies(iterable $classes): array
	{
		do
		{
			$previous = $classes;

			// Add missing parent classes, interfaces and traits

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

			$classes = array_unique($merged);

			// Add missing classes from typed properties, method arguments and return types

			$merged = [];

			foreach($classes as $class)
			{
				$merged[] = $class;

				$reflection = new ReflectionClass($class);

				foreach($reflection->getProperties() as $property)
				{
					if(($type = $property->getType()) !== null)
					{
						$merged = [...$merged, ...$this->getTypeClasses($type)];
					}
				}

				foreach($reflection->getMethods() as $method)
				{
					foreach($method->getParameters() as $parameter)
					{
						if(($type = $parameter->getType()) !== null)
						{
							$merged = [...$merged, ...$this->getTypeClasses($type)];
						}
					}

					if(($type = $method->getReturnType()) !== null)
					{
						$merged = [...$merged, ...$this->getTypeClasses($type)];
					}
				}
			}

			$classes = array_unique($merged);
		}
		while($classes !== $previous);

		// Return the complete list of classes to preload

		return $classes;
	}

	/**
	 * Returns an array containing the file paths of the provided classes.
	 *
	 * @param  array $classes An array of class names
	 * @return array
	 */
	protected function getClassFilePaths(array $classes): array
	{
		return array_map(static fn ($class) => (new ReflectionClass($class))->getFileName(), $classes);
	}

	/**
	 * Generates a preloader.
	 *
	 * @param  iterable $classes An iterable of class names
	 * @return string
	 */
	public function generatePreloader(iterable $classes): string
	{
		$classes = $this->getClassFilePaths($this->addMissingDependencies($classes));

		sort($classes);

		return sprintf($this->template, var_export($classes, true));
	}
}
