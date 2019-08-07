<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\cli\input\arguments\exceptions\UnexpectedValueException;
use mako\cli\input\Input;
use mako\cli\output\helpers\Table;
use mako\cli\output\Output;
use mako\common\traits\SuggestionTrait;
use mako\syringe\Container;
use ReflectionClass;

use function array_keys;
use function array_map;
use function ksort;
use function var_export;

/**
 * Reactor.
 *
 * @author Frederic G. Østby
 */
class Reactor
{
	use SuggestionTrait;

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
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Dispatcher.
	 *
	 * @var \mako\reactor\Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Commands.
	 *
	 * @var array
	 */
	protected $commands = [];

	/**
	 * Logo.
	 *
	 * @var string
	 */
	protected $logo;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\input\Input         $input      Input
	 * @param \mako\cli\output\Output       $output     Output
	 * @param \mako\syringe\Container|null  $container  Container
	 * @param \mako\reactor\Dispatcher|null $dispatcher Command dispatcher
	 */
	public function __construct(Input $input, Output $output, ?Container $container = null, ?Dispatcher $dispatcher = null)
	{
		$this->input = $input;

		$this->output = $output;

		$this->container = $container ?? new Container;

		$this->dispatcher = $dispatcher ?? new Dispatcher($this->container);

		// Register default reactor arguments

		$this->input->getArgumentParser()->addArguments
		([
			new Argument('command', 'Command name', Argument::IS_OPTIONAL),
			new Argument('--help', 'Displays helpful information', Argument::IS_BOOL),
		]);

		// Preparse arguments and ignore unknown arguments so that we
		// don't trigger any errors before the command arguments get registered

		$this->input->getArgumentParser()->parse(true);
	}

	/**
	 * Returns the input.
	 *
	 * @return \mako\cli\input\Input
	 */
	public function getInput(): Input
	{
		return $this->input;
	}

	/**
	 * Returns the output.
	 *
	 * @return \mako\cli\input\Output
	 */
	public function getOutput(): Output
	{
		return $this->output;
	}

	/**
	 * Registers a command.
	 *
	 * @param string $command Command
	 * @param string $class   Command class
	 */
	public function registerCommand(string $command, string $class): void
	{
		$this->commands[$command] = $class;
	}

	/**
	 * Sets the reactor logo.
	 *
	 * @param string $logo ASCII logo
	 */
	public function setLogo(string $logo): void
	{
		$this->logo = $logo;
	}

	/**
	 * Draws information table.
	 *
	 * @param string $heading Table heading
	 * @param array  $headers Table headers
	 * @param array  $rows    Table rows
	 */
	protected function drawTable(string $heading, array $headers, array $rows): void
	{
		if(!empty($rows))
		{
			$this->output->write(PHP_EOL);

			$this->output->writeLn("<yellow>{$heading}</yellow>");

			$this->output->write(PHP_EOL);

			$table = new Table($this->output);

			$headers = array_map(function($value) { return "<green>{$value}</green>"; }, $headers);

			$table->draw($headers, $rows);
		}
	}

	/**
	 * Draws an argument table.
	 *
	 * @param  string $heading   Table heading
	 * @param  array  $arguments Arguments
	 * @return void
	 */
	public function drawArgumentTable(string $heading, array $arguments): void
	{
		$argInfo = [];

		foreach($arguments as $argument)
		{
			$argInfo[] =
			[
				implode(' | ', array_filter([$argument->getAlias(), $argument->getName()])),
				$argument->getDescription(),
				$argument->isOptional() ? 'Yes' : 'No',
			];
		}

		$this->drawTable($heading, ['Name', 'Description', 'Optional'], $argInfo);
	}

	/**
	 * Displays basic reactor information.
	 */
	protected function displayReactorInfo(): void
	{
		// Display basic reactor information

		if(!empty($this->logo))
		{
			$this->output->writeLn($this->logo);

			$this->output->write(PHP_EOL);
		}

		$this->output->writeLn('<yellow>Usage:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn('php reactor [command] [arguments] [options]');

		// Display global arguments and options if there are any

		$this->drawArgumentTable('Global arguments and options:', $this->input->getArgumentParser()->getArguments());
	}

	/**
	 * Instantiates command without calling the constructor.
	 *
	 * @param  string                         $class Class name
	 * @return \mako\reactor\CommandInterface
	 */
	protected function instantiateCommandWithoutConstructor(string $class): CommandInterface
	{
		return (new ReflectionClass($class))->newInstanceWithoutConstructor();
	}

	/**
	 * Returns an array of command information.
	 *
	 * @return array
	 */
	protected function getCommands(): array
	{
		$info = [];

		foreach($this->commands as $name => $class)
		{
			$command = $this->instantiateCommandWithoutConstructor($class);

			$info[$name] = [$name, $command->getDescription()];
		}

		ksort($info);

		return $info;
	}

	/**
	 * Lists available commands if there are any.
	 */
	protected function listCommands(): void
	{
		$commands = $this->getCommands();

		$this->drawTable('Available commands:', ['Command', 'Description'], $commands);
	}

	/**
	 * Displays reactor info and lists all available commands.
	 *
	 * @return int
	 */
	protected function displayReactorInfoAndCommandList(): int
	{
		$this->displayReactorInfo();

		$this->listCommands();

		return CommandInterface::STATUS_SUCCESS;
	}

	/**
	 * Converst the argument and options arrays to table rows.
	 *
	 * @param  array $input Argument or option array
	 * @return array
	 */
	protected function convertArgumentsAndOptionsArrayToRows(array $input): array
	{
		$rows = [];

		foreach($input as $name => $info)
		{
			$rows[] = [$name, $info['description'], var_export($info['optional'], true)];
		}

		return $rows;
	}

	/**
	 * Displays information about the chosen command.
	 *
	 * @param  string $command Command
	 * @return int
	 */
	protected function displayCommandHelp(string $command): int
	{
		$commandInstance = $this->instantiateCommandWithoutConstructor($this->commands[$command]);

		$this->output->writeLn('<yellow>Command:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn("php reactor {$command}");

		$this->output->write(PHP_EOL);

		$this->output->writeLn('<yellow>Description:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn($commandInstance->getDescription());

		$this->drawArgumentTable('Arguments and options:', $commandInstance->getArguments());

		return CommandInterface::STATUS_SUCCESS;
	}

	/**
	 * Returns true if the command exists and false if not.
	 *
	 * @param  string $command Command
	 * @return bool
	 */
	protected function commandExists(string $command): bool
	{
		return isset($this->commands[$command]);
	}

	/**
	 * Displays error message for unknown commands.
	 *
	 * @param  string $command Command
	 * @return int
	 */
	protected function unknownCommand(string $command): int
	{
		$message = "Unknown command [ {$command} ].";

		if(($suggestion = $this->suggest($command, array_keys($this->commands))) !== null)
		{
			$message .= " Did you mean [ {$suggestion} ]?";
		}

		$this->output->writeLn("<red>{$message}</red>");

		$this->listCommands();

		return CommandInterface::STATUS_ERROR;
	}

	/**
	 * Dispatches a command.
	 *
	 * @param  string $command Command
	 * @return int
	 */
	protected function dispatch(string $command): int
	{
		try
		{
			$exitCode = $this->dispatcher->dispatch($this->commands[$command], $this->input->getArguments());
		}
		catch(ArgumentException | UnexpectedValueException $e)
		{
			$this->output->errorLn("<red>{$e->getMessage()}</red>");
		}

		return $exitCode ?? CommandInterface::STATUS_ERROR;
	}

	/**
	 * Registers the command arguments and dispatches the command.
	 *
	 * @param  string $command
	 * @return int
	 */
	protected function registerCommandArgumentsAndDispatch(string $command): int
	{
		$commandInstance = $this->instantiateCommandWithoutConstructor($this->commands[$command]);

		$this->input->getArgumentParser()->addArguments($commandInstance->getArguments());

		return $this->dispatch($command);
	}

	/**
	 * Run the reactor.
	 *
	 * @return int
	 */
	public function run(): int
	{
		$command = $this->input->getArgument('command');

		if($command === null)
		{
			return $this->displayReactorInfoAndCommandList();
		}

		if($this->commandExists($command) === false)
		{
			return $this->unknownCommand($command);
		}

		if($this->input->getArgument('--help') === true)
		{
			return $this->displayCommandHelp($command);
		}

		return $this->registerCommandArgumentsAndDispatch($command);
	}
}
