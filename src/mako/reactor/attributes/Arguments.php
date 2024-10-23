<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor\attributes;

use Attribute;
use mako\cli\input\arguments\Argument;

/**
 * Arguments attribute.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Arguments
{
	/**
	 * Arguments.
	 *
	 * @var Argument[]
	 */
	protected array $arguments;

	/**
	 * Constructor.
	 */
	public function __construct(Argument ...$arguments)
	{
		$this->arguments = $arguments;
	}

	/**
	 * Returns the command name.
	 */
	public function getArguments(): array
	{
		return $this->arguments;
	}
}
