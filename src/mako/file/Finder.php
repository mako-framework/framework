<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\file;

use FilesystemIterator;
use Generator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

/**
 * Finder.
 */
class Finder
{
	/**
	 * The paths that we should search.
	 *
	 * @var array
	 */
	protected $paths;

	/**
	 * The pattern that the files should match.
	 *
	 * @var string
	 */
	protected $pattern;

	/**
	 * Maximum search depth.
	 *
	 * @var int
	 */
	protected $maxDepth;

	/**
	 * Constructor.
	 *
	 * @param array $paths The paths that we should search
	 */
	public function __construct(array $paths)
	{
		$this->paths = $paths;
	}

	/**
	 * Sets a pattern that the files should match.
	 *
	 * @param  string $pattern
	 * @return $this
	 */
	public function setPattern(string $pattern)
	{
		$this->pattern = $pattern;

		return $this;
	}

	/**
	 * Returns the pattern that the files should match.
	 *
	 * @return string|null
	 */
	public function getPattern(): ?string
	{
		return $this->pattern;
	}

	/**
	 * Sets the maximum search depth.
	 *
	 * @param  int   $maxDepth
	 * @return $this
	 */
	public function setMaxDepth(int $maxDepth)
	{
		$this->maxDepth = $maxDepth;

		return $this;
	}

	/**
	 * Returns the the maximum search depth.
	 *
	 * @return int|null
	 */
	public function getMaxDepth(): ?int
	{
		return $this->maxDepth;
	}

	/**
	 * Creates an iterator instance.
	 *
	 * @param  string    $path
	 * @return \Iterator
	 */
	protected function createIterator(string $path): Iterator
	{
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS));

		if($this->maxDepth !== null)
		{
			$iterator->setMaxDepth($this->maxDepth);
		}

		if($this->pattern === null)
		{
			return $iterator;
		}

		return new RegexIterator($iterator, $this->pattern);
	}

	/**
	 * Finds all files in the given paths.
	 *
	 * @return \Generator
	 */
	public function find(): Generator
	{
		foreach($this->paths as $path)
		{
			yield from $this->createIterator($path);
		}
	}

	/**
	 * Finds all files in the given paths.
	 *
	 * @param  string     $className Class name
	 * @return \Generator
	 */
	public function findAs(string $className): Generator
	{
		foreach($this->find() as $file)
		{
			yield $file => new $className($file);
		}
	}
}
