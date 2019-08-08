<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\cli\input\arguments\Argument;
use mako\cli\input\Input;
use mako\cli\output\Output;
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
	 * Command description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Command information.
	 *
	 * @deprecated 7.0
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => '',
		'arguments'   => [],
		'options'     => [],
	];

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
	public function getDescription(): string
	{
		return $this->description ?? $this->commandInformation['description'] ?? '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		$arguments = [];

		if(!empty($this->commandInformation['arguments']))
		{
			foreach($this->commandInformation['arguments'] as $name => $argument)
			{
				$arguments[] = new Argument($name, $argument['description'], $argument['optional'] ? Argument::IS_OPTIONAL : 0);
			}
		}

		if(!empty($this->commandInformation['options']))
		{
			foreach($this->commandInformation['options'] as $name => $argument)
			{
				$arguments[] = new Argument("--{$name}", $argument['description'], $argument['optional'] ? Argument::IS_OPTIONAL : 0);
			}
		}

		return $arguments;
	}
}
