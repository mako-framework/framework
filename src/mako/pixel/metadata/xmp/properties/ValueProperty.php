<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

use mako\pixel\metadata\xmp\properties\traits\NameTrait;

/**
 * Value property.
 */
class ValueProperty
{
	use NameTrait;

	 /**
	  * Constructor.
	  */
	 public function __construct(
		public protected(set) string $schema,
		public protected(set) int $options,
		public protected(set) string $fullyQualifiedName,
		public protected(set) string $value,
		public protected(set) array $qualifiers = []
	) {
	}
}
