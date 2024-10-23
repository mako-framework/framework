<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\attributes;

use Attribute;

/**
 * Command description attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CommandDescription
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $description
	) {
	}

	/**
	 * Returns the command description.
	 */
	public function getDescription(): string
	{
		return $this->description;
	}
}
