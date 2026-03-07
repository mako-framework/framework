<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\classes;

use Generator;
use mako\file\Finder;

use function array_diff;
use function count;
use function in_array;
use function is_array;
use function is_string;
use function php_strip_whitespace;
use function token_get_all;

/**
 * Class finder.
 */
class ClassFinder
{
	/**
	 * PHP filename pattern.
	 */
	protected const string PHP_FILENAME_PATTERN = '/\.php$/';

	/**
	 * Should classes be included?
	 */
	protected bool $includeClasses = true;

	/**
	 * Should abstract classes be included?
	 */
	protected bool $includeAbstractClasses = true;

	/**
	 * Should interfaces be included?
	 */
	protected bool $includeInterfaces = true;

	/**
	 * Should enums be included?
	 */
	protected bool $includeEnums = true;

	/**
	 * Should traits be included?
	 */
	protected bool $includeTraits = true;

	/**
	 * Interfaces to filter on.
	 */
	protected array $implementing = [];

	/**
	 * Parent classes to filter on.
	 */
	protected array $extending = [];

	/**
	 * Traits to filter on.
	 */
	protected array $using = [];

	/**
	 * Attributes to filter on.
	 */
	protected array $with = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Finder $finder
	) {
		if ($this->finder->getPattern() === null) {
			$this->finder->setPattern(static::PHP_FILENAME_PATTERN);
		}
	}

	/**
	 * Includes classes.
	 *
	 * @return $this
	 */
	public function includeClasses(): static
	{
		$this->includeClasses = true;

		return $this;
	}

	/**
	 * Excludes classes.
	 *
	 * @return $this
	 */
	public function excludeClasses(): static
	{
		$this->includeClasses = false;

		return $this;
	}

	/**
	 * Includes abstract classes.
	 *
	 * @return $this
	 */
	public function includeAbstractClasses(): static
	{
		$this->includeAbstractClasses = true;

		return $this;
	}

	/**
	 * Excludes abstract classes.
	 *
	 * @return $this
	 */
	public function excludeAbstractClasses(): static
	{
		$this->includeAbstractClasses = false;

		return $this;
	}

	/**
	 * Includes interfaces.
	 *
	 * @return $this
	 */
	public function includeInterfaces(): static
	{
		$this->includeInterfaces = true;

		return $this;
	}

	/**
	 * Excludes interfaces.
	 *
	 * @return $this
	 */
	public function excludeInterfaces(): static
	{
		$this->includeInterfaces = false;

		return $this;
	}

	/**
	 * Includes enums.
	 *
	 * @return $this
	 */
	public function includeEnums(): static
	{
		$this->includeEnums = true;

		return $this;
	}

	/**
	 * Excludes enums.
	 *
	 * @return $this
	 */
	public function excludeEnums(): static
	{
		$this->includeEnums = false;

		return $this;
	}

	/**
	 * Includes traits.
	 *
	 * @return $this
	 */
	public function includeTraits(): static
	{
		$this->includeTraits = true;

		return $this;
	}

	/**
	 * Excludes traits.
	 *
	 * @return $this
	 */
	public function excludeTraits(): static
	{
		$this->includeTraits = false;

		return $this;
	}

	/**
	 * Filters on interfaces.
	 */
	public function implementing(array $interfaces): static
	{
		$this->implementing = $interfaces;

		return $this;
	}

	/**
	 * Filters on parent classes.
	 */
	public function extending(array $classes): static
	{
		$this->extending = $classes;

		return $this;
	}

	/**
	 * Filters on traits.
	 */
	public function using(array $traits): static
	{
		$this->using = $traits;

		return $this;
	}

	/**
	 * Filters on attributes.
	 */
	public function with(array $attributes): static
	{
		$this->with = $attributes;

		return $this;
	}

	/**
	 * Returns the tokens we're searching for.
	 */
	protected function getAllowedClasslikeTokens(): array
	{
		$tokens = [];

		if ($this->includeClasses) {
			$tokens[] = T_CLASS;
		}

		if ($this->includeInterfaces) {
			$tokens[] = T_INTERFACE;
		}

		if ($this->includeEnums) {
			$tokens[] = T_ENUM;
		}

		if ($this->includeTraits) {
			$tokens[] = T_TRAIT;
		}

		return $tokens;
	}

	/**
	 * Finds the class in a PHP file.
	 */
	protected function findClassInFile(string $path): ?string
	{
		$allowedClasslikeTokens = $this->getAllowedClasslikeTokens();

		$tokens = token_get_all(php_strip_whitespace($path));

		$tokenCount = count($tokens);

		$namespace = '';

		for ($i = 0; $i < $tokenCount; $i++) {
			if (is_string($tokens[$i])) {
				continue;
			}

			if (T_NAMESPACE === $tokens[$i][0]) {
				while (isset($tokens[++$i]) && is_array($tokens[$i])) {
					if (in_array($tokens[$i][0], [T_STRING, T_NAME_QUALIFIED])) {
						$namespace .= $tokens[$i][1];
					}
				}

				$namespace .= '\\';
			}

			if (in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_ENUM, T_TRAIT]) && T_WHITESPACE === $tokens[$i + 1][0] && T_STRING === $tokens[$i + 2][0]) {
				if (in_array($tokens[$i][0], $allowedClasslikeTokens) && ($this->includeAbstractClasses || (!isset($tokens[$i - 2]) || !is_array($tokens[$i - 2]) || $tokens[$i - 2][0] !== T_ABSTRACT))) {
					return $namespace . $tokens[$i + 2][1];
				}

				return null;
			}
		}

		return null;
	}

	/**
	 * Returns all the classes.
	 */
	protected function findClasses(): Generator
	{
		/** @var string $file */
		foreach ($this->finder->find() as $file) {
			if (($class = $this->findClassInFile($file)) !== null) {
				if ($this->implementing !== []) {
					if (!empty(array_diff($this->implementing, ClassInspector::getInterfaces($class)))) {
						continue;
					}
				}

				if ($this->extending !== []) {
					if (!empty(array_diff($this->extending, ClassInspector::getParents($class)))) {
						continue;
					}
				}

				if ($this->using !== []) {
					if (!empty(array_diff($this->using, ClassInspector::getTraits($class)))) {
						continue;
					}
				}

				if ($this->with !== []) {
					if (!empty(array_diff($this->with, ClassInspector::getAttributes($class)))) {
						continue;
					}
				}

				yield $file => $class;
			}
		}
	}

	/**
	 * Returns all the classes.
	 */
	public function find(): Generator
	{
		yield from $this->findClasses();
	}

	/**
	 * Returns all the classes implementing the interface.
	 */
	public function findImplementing(string $interfaceName): Generator
	{
		$this->implementing([$interfaceName]);

		yield from $this->findClasses();

		$this->implementing([]);
	}

	/**
	 * Returns all the classes extending the class.
	 */
	public function findExtending(string $className): Generator
	{
		$this->extending([$className]);

		yield from $this->findClasses();

		$this->extending([]);
	}

	/**
	 * Returns all the classes using the trait.
	 */
	public function findUsing(string $traitName): Generator
	{
		$this->using([$traitName]);

		yield from $this->findClasses();

		$this->using([]);
	}

	/**
	 * Returns all the classes with the attribute.
	 */
	public function findWith(string $attributeName): Generator
	{
		$this->with([$attributeName]);

		yield from $this->findClasses();

		$this->with([]);
	}
}
