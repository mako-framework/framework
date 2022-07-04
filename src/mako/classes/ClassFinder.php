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
	 *
	 * @var string
	 */
	public const PHP_FILENAME_PATTERN = '/\.php$/';

	/**
	 * Finder instance.
	 *
	 * @var \mako\file\Finder
	 */
	protected $finder;

	/**
	 * Should classes be included?
	 *
	 * @var bool
	 */
	protected $includeClasses = true;

	/**
	 * Should abstract classes be included?
	 *
	 * @var bool
	 */
	protected $includeAbstractClasses = true;

	/**
	 * Should interfaces be included?
	 *
	 * @var bool
	 */
	protected $includeInterfaces = true;

	/**
	 * Should traits be included?
	 *
	 * @var bool
	 */
	protected $includeTraits = true;

	/**
	 * Constructor.
	 *
	 * @param \mako\file\Finder $finder Finder instance
	 */
	public function __construct(Finder $finder)
	{
		if($finder->getPattern() === null)
		{
			$finder->setPattern(static::PHP_FILENAME_PATTERN);
		}

		$this->finder = $finder;
	}

	/**
	 * Includes classes.
	 *
	 * @return $this
	 */
	public function includeClasses()
	{
		$this->includeClasses = true;

		return $this;
	}

	/**
	 * Excludes classes.
	 *
	 * @return $this
	 */
	public function excludeClasses()
	{
		$this->includeClasses = false;

		return $this;
	}

	/**
	 * Includes abstract classes.
	 *
	 * @return $this
	 */
	public function includeAbstractClasses()
	{
		$this->includeAbstractClasses = true;

		return $this;
	}

	/**
	 * Excludes abstract classes.
	 *
	 * @return $this
	 */
	public function excludeAbstractClasses()
	{
		$this->includeAbstractClasses = false;

		return $this;
	}

	/**
	 * Includes interfaces.
	 *
	 * @return $this
	 */
	public function includeInterfaces()
	{
		$this->includeInterfaces = true;

		return $this;
	}

	/**
	 * Excludes interfaces.
	 *
	 * @return $this
	 */
	public function excludeInterfaces()
	{
		$this->includeInterfaces = false;

		return $this;
	}

	/**
	 * Includes traits.
	 *
	 * @return $this
	 */
	public function includeTraits()
	{
		$this->includeTraits = true;

		return $this;
	}

	/**
	 * Excludes traits.
	 *
	 * @return $this
	 */
	public function excludeTraits()
	{
		$this->includeTraits = false;

		return $this;
	}

	/**
	 * Returns the tokens we're searching for.
	 *
	 * @return array
	 */
	protected function getAllowedClasslikeTokens(): array
	{
		$tokens = [];

		if($this->includeClasses)
		{
			$tokens[] = T_CLASS;
		}

		if($this->includeInterfaces)
		{
			$tokens[] = T_INTERFACE;
		}

		if($this->includeTraits)
		{
			$tokens[] = T_TRAIT;
		}

		return $tokens;
	}

	/**
	 * Finds the class in a PHP file.
	 *
	 * @param  string      $path Path to PHP file
	 * @return string|null
	 */
	protected function findClassInFile(string $path): ?string
	{
		$allowedClasslikeTokens = $this->getAllowedClasslikeTokens();

		$tokens = token_get_all(php_strip_whitespace($path));

		$tokenCount = count($tokens);

		$namespace = '';

		for($i = 0; $i < $tokenCount; $i++)
		{
			if(is_string($tokens[$i]))
			{
				continue;
			}

			if(T_NAMESPACE === $tokens[$i][0])
			{
				while(isset($tokens[++$i]) && is_array($tokens[$i]))
				{
					if(in_array($tokens[$i][0], [T_STRING, T_NAME_QUALIFIED]))
					{
						$namespace .= $tokens[$i][1];
					}
				}

				$namespace .= '\\';
			}

			if(in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_TRAIT]) && T_WHITESPACE === $tokens[$i + 1][0] && T_STRING === $tokens[$i + 2][0])
			{
				if(in_array($tokens[$i][0], $allowedClasslikeTokens) && ($this->includeAbstractClasses || (!isset($tokens[$i - 2]) || !is_array($tokens[$i - 2]) || $tokens[$i - 2][0] !== T_ABSTRACT)))
				{
					return $namespace . $tokens[$i + 2][1];
				}

				return null;
			}
		}

		return null;
	}

	/**
	 * Returns all the classes.
	 *
	 * @return \Generator
	 */
	protected function findClasses(): Generator
	{
		/** @var string $file */
		foreach($this->finder->find() as $file)
		{
			if(($class = $this->findClassInFile($file)) !== null)
			{
				yield $class;
			}
		}
	}

	/**
	 * Returns all the classes.
	 *
	 * @return \Generator
	 */
	public function find(): Generator
	{
		yield from $this->findClasses();
	}

	/**
	 * Returns all the classes implementing the interface.
	 *
	 * @param  string     $interfaceName Interface name
	 * @return \Generator
	 */
	public function findImplementing(string $interfaceName): Generator
	{
		foreach($this->findClasses() as $class)
		{
			if(is_subclass_of($class, $interfaceName))
			{
				yield $class;
			}
		}
	}

	/**
	 * Returns all the classes extending the class.
	 *
	 * @param  string     $className Class name
	 * @return \Generator
	 */
	public function findExtending(string $className): Generator
	{
		foreach($this->findClasses() as $class)
		{
			if(is_subclass_of($class, $className))
			{
				yield $class;
			}
		}
	}

	/**
	 * Returns all the classes using the trait.
	 *
	 * @param  string     $traitName Trait name
	 * @return \Generator
	 */
	public function findUsing(string $traitName): Generator
	{
		foreach($this->findClasses() as $class)
		{
			if(isset(ClassInspector::getTraits($class)[$traitName]))
			{
				yield $class;
			}
		}
	}
}
