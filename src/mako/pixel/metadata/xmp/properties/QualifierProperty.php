<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

use mako\pixel\metadata\xmp\properties\traits\NameTrait;

/**
 * Qualifier property.
 */
class QualifierProperty
{
	use NameTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		public protected(set) string $namespace,
		public protected(set) int $options,
		public protected(set) string $fullyQualifiedName,
		public protected(set) string $value
	) {
	}
}
