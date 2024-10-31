<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments;

use mako\cli\input\arguments\exceptions\ArgumentException;

use function str_starts_with;

/**
 * Positional argument.
 */
class PositionalArgument extends Argument
{
	/**
	 * Constructor.
	 */
	public function __construct(string $name, string $description = '', int $options = 0, mixed $default = null)
	{
		if (str_starts_with($name, '-')) {
			throw new ArgumentException('Positional argument names cannot start with "-".');
		}

		parent::__construct($name, $description, $options, $default);
	}
}
