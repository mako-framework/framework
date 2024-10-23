<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\attributes;

use Attribute;

/**
 * Command attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Command
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $name,
		protected string $description = '',
	) {
	}

	/**
	 * Returns the command name.
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns the command description.
	 */
	public function getDescription(): string
	{
		return $this->description;
	}
}
