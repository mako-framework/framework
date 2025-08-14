<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

use Override;

/**
 * Abstract type.
 */
abstract class Type implements TypeInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $value
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getValue(): mixed
	{
		return $this->value;
	}
}
