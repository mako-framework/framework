<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

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
	public function getValue(): mixed
	{
		return $this->value;
	}
}
