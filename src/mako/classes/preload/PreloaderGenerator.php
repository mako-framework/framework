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
	 */
	protected const string TEMPLATE = <<<'EOF'
	<?php

	$files = %s;

	foreach($files as $file)
	{
		opcache_compile_file($file);
	}

	EOF;

	/**
	 * Returns the user defined attributes.
	 */
	protected function getUserDefinedAttributes(array $attributes): array
	{
		$userDefinedAttributes = [];

		foreach ($attributes as $attribute) {
			$name = $attribute->getName();

			if ((new ReflectionClass($name))->isUserDefined()) {
				$userDefinedAttributes[] = $name;
			}
		}

		return $userDefinedAttributes;
	}

	/**
	 * Get class names from reflection type.
	 */
	protected function getTypeClasses(ReflectionType $type): array
	{
		$classes = [];

		if ($type instanceof ReflectionNamedType) {
			$class = $type->getName();

			if (!$type->isBuiltin() && (class_exists($class) || interface_exists($class)) && (new ReflectionClass($class))->isUserDefined()) {
				$classes[] = $class;
			}
		}
		elseif ($type instanceof ReflectionIntersectionType) {
			/** @var ReflectionNamedType $intersectionType */
			foreach ($type->getTypes() as $intersectionType) {
				$class = $intersectionType->getName();

				if ((class_exists($class) || interface_exists($class)) && (new ReflectionClass($class))->isUserDefined()) {
					$classes[] = $class;
				}
			}
		}
		elseif ($type instanceof ReflectionUnionType) {
			/** @var ReflectionIntersectionType|ReflectionNamedType $unionType */
			foreach ($type->getTypes() as $unionType) {
				if ($unionType instanceof ReflectionIntersectionType) {
					$classes = [...$classes, ...$this->getTypeClasses($unionType)];

					continue;
				}

				$class = $unionType->getName();

				if (!$unionType->isBuiltin() && (class_exists($class) || interface_exists($class)) && (new ReflectionClass($class))->isUserDefined()) {
					$classes[] = $class;
				}
			}
		}

		return $classes;
	}

	/**
	 * Adds missing user defined dependencies to the class array.
	 */
	protected function addMissingDependencies(iterable $classes): array
	{
		do {
			$previous = $classes;

			// Add missing attributes, parent classes, interfaces and traits

			$merged = [];

			foreach ($classes as $class) {
				$merged[] = $class;

				foreach (ClassInspector::getAttributes($class) as $attribute) {
					if ((new ReflectionClass($attribute))->isUserDefined()) {
						$merged[] = $attribute;
					}
				}

				foreach (ClassInspector::getParents($class) as $parent) {
					if ((new ReflectionClass($parent))->isUserDefined()) {
						$merged[] = $parent;
					}
				}

				foreach (ClassInspector::getInterfaces($class) as $interface) {
					if ((new ReflectionClass($interface))->isUserDefined()) {
						$merged[] = $interface;
					}
				}

				foreach (ClassInspector::getTraits($class) as $trait) {
					if ((new ReflectionClass($trait))->isUserDefined()) {
						$merged[] = $trait;
					}
				}
			}

			$classes = array_unique($merged);

			// Add missing classes from typed properties, method arguments and return types

			$merged = [];

			foreach ($classes as $class) {
				$merged[] = $class;

				$reflection = new ReflectionClass($class);

				foreach ($reflection->getProperties() as $property) {
					if (!empty(($attributes = $property->getAttributes()))) {
						$merged = [...$merged, ...$this->getUserDefinedAttributes($attributes)];
					}

					if (($type = $property->getType()) !== null) {
						$merged = [...$merged, ...$this->getTypeClasses($type)];
					}
				}

				foreach ($reflection->getMethods() as $method) {
					if (!empty(($attributes = $method->getAttributes()))) {
						$merged = [...$merged, ...$this->getUserDefinedAttributes($attributes)];
					}

					foreach ($method->getParameters() as $parameter) {
						if (!empty(($attributes = $parameter->getAttributes()))) {
							$merged = [...$merged, ...$this->getUserDefinedAttributes($attributes)];
						}

						if (($type = $parameter->getType()) !== null) {
							$merged = [...$merged, ...$this->getTypeClasses($type)];
						}
					}

					if (($type = $method->getReturnType()) !== null) {
						$merged = [...$merged, ...$this->getTypeClasses($type)];
					}
				}
			}

			$classes = array_unique($merged);
		}
		while ($classes !== $previous);

		// Return the complete list of classes to preload

		return $classes;
	}

	/**
	 * Returns an array containing the file paths of the provided classes.
	 */
	protected function getClassFilePaths(array $classes): array
	{
		return array_map(static fn ($class) => (new ReflectionClass($class))->getFileName(), $classes);
	}

	/**
	 * Generates a preloader.
	 */
	public function generatePreloader(iterable $classes): string
	{
		$classes = $this->getClassFilePaths($this->addMissingDependencies($classes));

		sort($classes);

		return sprintf(static::TEMPLATE, var_export($classes, true));
	}
}
