<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

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
	 * Input.
	 *
	 * @var \mako\cli\input\Input
	 */
	protected $input;

	/**
	 * Output.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Command.
	 *
	 * @var string|null
	 */
	protected $command;

	/**
	 * Command description.
	 *
	 * @var string|null
	 */
	protected $description;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input   $input  Input
	 * @param \mako\cli\output\Output $output Output
	 */
	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getCommand(): ?string
	{
		return $this->command ?? null;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDescription(): string
	{
		return $this->description ?? '';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return [];
	}
}
