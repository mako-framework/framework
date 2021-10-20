<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility\str;

use function count;

/**
 * String alternator.
 */
class Alternator
{
	/**
	 * Strings.
	 *
	 * @var array
	 */
	protected $strings;

	/**
	 * String count.
	 *
	 * @var int
	 */
	protected $stringCount;

	/**
	 * Counter.
	 *
	 * @var int
	 */
	protected $counter = 0;

	/**
	 * Constructor.
	 *
	 * @param array $strings Strings
	 */
	public function __construct(array $strings)
	{
		$this->strings = $strings;

		$this->stringCount = count($strings);
	}

	/**
	 * Returns a string.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->strings[$this->counter++ % $this->stringCount];
	}

	/**
	 * Returns a string.
	 *
	 * @return string
	 */
	public function __invoke(): string
	{
		return $this->__toString();
	}
}
