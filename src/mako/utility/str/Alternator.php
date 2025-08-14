<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility\str;

use Override;
use Stringable;

use function count;

/**
 * String alternator.
 */
class Alternator implements Stringable
{
	/**
	 * String count.
	 */
	protected int $stringCount;

	/**
	 * Counter.
	 */
	protected int $counter = 0;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $strings
	) {
		$this->stringCount = count($strings);
	}

	/**
	 * Returns a string.
	 */
	#[Override]
	public function __toString(): string
	{
		return $this->strings[$this->counter++ % $this->stringCount];
	}

	/**
	 * Returns a string.
	 */
	public function __invoke(): string
	{
		return $this->__toString();
	}
}
