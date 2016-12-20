<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

use mako\database\types\TypeInterface;

/**
 * Abstract type.
 *
 * @author Frederic G. Østby
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
	 * @access public
	 * @param mixed $value Value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType(): int
	{
		return static::TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return $this->value;
	}
}
