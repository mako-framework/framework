<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use Deprecated;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\reactor\traits\CommandHelperTrait;

/**
 * Base command.
 */
abstract class Command implements CommandInterface
{
	use CommandHelperTrait;

	/**
	 * Command.
	 */
	protected string $command;

	/**
	 * Command description.
	 */
	protected string $description;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Input $input,
		protected Output $output
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Deprecated('update your commands to use the "CommandName" attribute instead', since: 'Mako 11.0.0')]
	public function getCommand(): ?string
	{
		return $this->command ?? null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Deprecated('update your commands to use the "CommandDescription" attribute instead', since: 'Mako 11.0.0')]
	public function getDescription(): string
	{
		return $this->description ?? '';
	}

	/**
	 * {@inheritDoc}
	 */
	#[Deprecated('update your commands to use the "CommandArguments" attribute instead', since: 'Mako 11.0.0')]
	public function getArguments(): array
	{
		return [];
	}
}
