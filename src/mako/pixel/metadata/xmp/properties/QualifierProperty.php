<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

use mako\pixel\metadata\xmp\properties\traits\ShortNameTrait;

/**
 * Qualifier property.
 */
class QualifierProperty
{
	use ShortNameTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		public protected(set) string $schema,
		public protected(set) int $options,
		public protected(set) string $name,
		public protected(set) string $value
	) {
	}
}
