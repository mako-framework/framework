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
	 * Constructor.
	 */
	public function __construct(
		protected array $paths,
		protected ?string $pattern = null,
		protected ?int $maxDepth = null
	) {
	}

	/**
	 * Sets a pattern that the files should match.
	 *
	 * @return $this
	 */
	public function setPattern(string $pattern): static
	{
		$this->pattern = $pattern;

		return $this;
	}

	/**
	 * Returns the pattern that the files should match.
	 */
	public function getPattern(): ?string
	{
		return $this->pattern;
	}

	/**
	 * Sets the maximum search depth.
	 *
	 * @return $this
	 */
	public function setMaxDepth(int $maxDepth): static
	{
		$this->maxDepth = $maxDepth;

		return $this;
	}

	/**
	 * Returns the the maximum search depth.
	 */
	public function getMaxDepth(): ?int
	{
		return $this->maxDepth;
	}

	/**
	 * Creates an iterator instance.
	 */
	protected function createIterator(string $path): Iterator
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($path, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS)
		);

		if ($this->maxDepth !== null) {
			$iterator->setMaxDepth($this->maxDepth);
		}

		if ($this->pattern === null) {
			return $iterator;
		}

		return new RegexIterator($iterator, $this->pattern);
	}

	/**
	 * Finds all files in the given paths.
	 */
	public function find(): Generator
	{
		foreach ($this->paths as $path) {
			yield from $this->createIterator($path);
		}
	}

	/**
	 * Finds all files in the given paths.
	 */
	public function findAs(string $className): Generator
	{
		foreach ($this->find() as $file) {
			yield $file => new $className($file);
		}
	}
}
