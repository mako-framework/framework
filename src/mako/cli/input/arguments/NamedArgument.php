<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\arguments;

use function implode;

/**
 * Named argument.
 */
class NamedArgument extends Argument
{
	/**
	 * Constructor.
	 */
	public function __construct(string $name, ?string $alias = null, string $description = '', int $options = 0, mixed $default = null)
	{
		$names = [];

		if ($alias !== null) {
			$names[] = "-{$alias}";
		}

		$names[] = "--{$name}";

		$name = implode('|', $names);

		parent::__construct($name, $description, $options, $default);
	}
}
