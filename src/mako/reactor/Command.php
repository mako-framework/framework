<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\reactor\traits\CommandHelperTrait;
use Override;

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
	#[Override]
	public function getCommand(): ?string
	{
		return $this->command ?? null;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getDescription(): string
	{
		return $this->description ?? '';
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getArguments(): array
	{
		return [];
	}
}
