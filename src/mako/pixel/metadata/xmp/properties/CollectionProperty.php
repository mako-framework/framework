<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use mako\pixel\metadata\xmp\properties\traits\ShortNameTrait;
use Override;

use function count;

/**
 * Collection property base class.
 */
abstract class CollectionProperty implements Countable, IteratorAggregate
{
	use ShortNameTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		public protected(set) string $schema,
		public protected(set) int $options,
		public protected(set) string $name,
		public protected(set) array $values = []
	) {
	}

	/**
	 * Returns the numner of values.
	 */
	#[Override]
	public function count(): int
	{
		return count($this->values);
	}

	/**
	 * Returns the iterator.
	 */
	#[Override]
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->values);
	}
}
