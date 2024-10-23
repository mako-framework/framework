<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\attributes;

use Attribute;

/**
 * Command name attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CommandName
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $name
	) {
	}

	/**
	 * Returns the command name.
	 */
	public function getName(): string
	{
		return $this->name;
	}
}
