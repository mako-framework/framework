<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\classes;

use Generator;
use mako\file\Finder;

use function count;
use function in_array;
use function is_array;
use function is_string;
use function is_subclass_of;
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
				yield $class;
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
		foreach ($this->findClasses() as $class) {
			if (is_subclass_of($class, $interfaceName)) {
				yield $class;
			}
		}
	}

	/**
	 * Returns all the classes extending the class.
	 */
	public function findExtending(string $className): Generator
	{
		foreach ($this->findClasses() as $class) {
			if (is_subclass_of($class, $className)) {
				yield $class;
			}
		}
	}

	/**
	 * Returns all the classes using the trait.
	 */
	public function findUsing(string $traitName): Generator
	{
		foreach ($this->findClasses() as $class) {
			if (isset(ClassInspector::getTraits($class)[$traitName])) {
				yield $class;
			}
		}
	}
}
