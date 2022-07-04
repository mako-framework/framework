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
	 * Value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Constructor.
	 *
	 * @param mixed $value Value
	 */
	public function __construct(mixed $value)
	{
		$this->value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getValue()
	{
		return $this->value;
	}
}
