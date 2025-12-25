<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

use mako\pixel\metadata\xmp\properties\traits\NameTrait;
use Override;
use Stringable;

/**
 * Value property.
 */
class ValueProperty implements Stringable
{
	use NameTrait;

	 /**
	  * Constructor.
	  */
	 public function __construct(
		public protected(set) string $namespace,
		public protected(set) int $options,
		public protected(set) string $fullyQualifiedName,
		public protected(set) string $value,
		public protected(set) array $qualifiers = []
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function __toString(): string
	{
		return $this->value;
	}
}
