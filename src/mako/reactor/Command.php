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
	 * Should the command be executed?
	 *
	 * @var bool
	 */
	protected $shouldExecute = true;

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

		if($this->input->getArgument('help') === true)
		{
			$this->displayCommandDetails();
		}
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

	/**
	 * {@inheritdoc}
	 */
	public function shouldExecute(): bool
	{
		return $this->shouldExecute;
	}

	/**
	 * Draws an info table.
	 *
	 * @param array $items Items
	 */
	protected function drawInfoTable(array $items)
	{
		$headers = ['Name', 'Description', 'Optional'];

		$rows = [];

		foreach($items as $name => $argument)
		{
			$rows[] = [$name, $argument['description'], var_export($argument['optional'], true)];
		}

		$this->table($headers, $rows);
	}

	/**
	 * Displays command details.
	 */
	protected function displayCommandDetails()
	{
		$this->write('<yellow>Command:</yellow>');

		$this->nl();

		$this->write('php reactor ' . $this->input->getArgument(1));

		$this->nl();

		$this->write('<yellow>Description:</yellow>');

		$this->nl();

		$this->write($this->getCommandDescription());

		if(!empty($this->commandInformation['arguments']))
		{
			$this->nl();

			$this->write('<yellow>Arguments:</yellow>');

			$this->nl();

			$this->drawInfoTable($this->commandInformation['arguments']);
		}

		if(!empty($this->commandInformation['options']))
		{
			$this->nl();

			$this->write('<yellow>Options:</yellow>');

			$this->nl();

			$this->drawInfoTable($this->commandInformation['options']);
		}

		$this->shouldExecute = false;
	}
}
