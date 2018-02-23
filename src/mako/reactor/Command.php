<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\reactor\CommandInterface;
use mako\reactor\traits\CommandHelperTrait;

/**
 * Base command.
 *
 * @author Frederic G. Østby
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
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => '',
		'arguments'   => [],
		'options'     => [],
	];

	/**
	 * Should we be strict about what arguments and options we allow?
	 *
	 * @var bool
	 */
	protected $isStrict = false;

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
	 * {@inheritdoc}
	 */
	public function getCommandDescription(): string
	{
		return $this->commandInformation['description'] ?? '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCommandArguments(): array
	{
		return $this->commandInformation['arguments'] ?? [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCommandOptions(): array
	{
		return $this->commandInformation['options'] ?? [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStrict(): bool
	{
		return $this->isStrict;
	}
}
