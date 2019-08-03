<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use Closure;
use mako\cli\input\Input;
use mako\cli\output\helpers\Table;
use mako\cli\output\Output;
use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\reactor\traits\SuggestionTrait;
use mako\syringe\Container;
use ReflectionClass;

use function array_keys;
use function array_map;
use function array_merge;
use function ksort;
use function sort;
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
	 * Options.
	 *
	 * @var array
	 */
	protected $options = [];

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
	 * Register a global reactor option.
	 *
	 * @param string   $name        Option name
	 * @param string   $description Option description
	 * @param \Closure $handler     Option handler
	 * @param string   $group       Option group
	 */
	public function registerGlobalOption(string $name, string $description, Closure $handler, string $group = 'default'): void
	{
		$this->options[$group][$name] = ['description' => $description, 'handler' => $handler];
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
	 * Handles global reactor options.
	 *
	 * @param string $group Option group
	 */
	public function handleGlobalOptions(string $group = 'default'): void
	{
		if(isset($this->options[$group]))
		{
			foreach($this->options[$group] as $name => $option)
			{
				$input = $this->input->getArgument($name);

				if(!empty($input))
				{
					$handler = $option['handler'];

					$this->container->call($handler, ['option' => $input]);
				}
			}
		}
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
	 * Returns an array of option information.
	 *
	 * @return array
	 */
	protected function getOptions(): array
	{
		$options = [];

		foreach($this->options as $group)
		{
			foreach($group as $name => $option)
			{
				$options[] = ["--{$name}", $option['description']];
			}
		}

		sort($options);

		return $options;
	}

	/**
	 * Displays global reactor options of there are any.
	 */
	protected function listOptions(): void
	{
		$options = $this->getOptions();

		$this->drawTable('Global options:', ['Option', 'Description'], $options);
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

		// Display reactor options if there are any

		$this->listOptions();
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

			$info[$name] = [$name, $command->getCommandDescription()];
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
	 * Converts the argument and options arrays to table rows.
	 *
	 * @param  array $input Argument array
	 * @return array
	 */
	protected function convertArgumentsToRows(array $input): array
	{
		$rows = [];

		foreach($input as $name => $info)
		{
			$rows[] = [$name, $info['description'], var_export($info['optional'], true)];
		}

		return $rows;
	}

	/**
	 * Converts the argument and options arrays to table rows.
	 *
	 * @param  array $input Option array
	 * @return array
	 */
	protected function convertOptionsToRows(array $input): array
	{
		$rows = [];

		foreach($input as $name => $info)
		{
			$rows[] = [$name, (isset($info['shorthand']) ? '-' . $info['shorthand'] : ''), $info['description'], var_export($info['optional'], true)];
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

		$this->output->writeLn($commandInstance->getCommandDescription());

		if(!empty($arguments = $commandInstance->getCommandArguments()))
		{
			$this->drawTable('Arguments:', ['Name', 'Description', 'Optional'], $this->convertArgumentsToRows($arguments));
		}

		if(!empty($options = $commandInstance->getCommandOptions()))
		{
			$this->drawTable('Options:', ['Name', 'Shorthand', 'Description', 'Optional'], $this->convertOptionsToRows($options));
		}

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
	 * Returns the names of the global options.
	 *
	 * @return array
	 */
	protected function getGlobalOptionNames(): array
	{
		$names = [];

		foreach($this->options as $group)
		{
			$names = array_merge($names, array_keys($group));
		}

		return $names;
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
			$exitCode = $this->dispatcher->dispatch($this->commands[$command], $this->input->getArguments(), $this->getGlobalOptionNames());
		}
		catch(InvalidOptionException $e)
		{
			$this->output->errorLn("<red>{$e->getMessageWithSuggestion()}</red>");
		}
		catch(InvalidArgumentException | MissingOptionException | MissingArgumentException $e)
		{
			$this->output->errorLn("<red>{$e->getMessage()}</red>");
		}

		return $exitCode ?? CommandInterface::STATUS_ERROR;
	}

	/**
	 * Run the reactor.
	 *
	 * @return int
	 */
	public function run(): int
	{
		$this->handleGlobalOptions();

		if(($command = $this->input->getArgument(1)) === null)
		{
			return $this->displayReactorInfoAndCommandList();
		}

		if($this->commandExists($command) === false)
		{
			return $this->unknownCommand($command);
		}

		if($this->input->getArgument('help', false) !== false)
		{
			return $this->displayCommandHelp($command);
		}

		return $this->dispatch($command);
	}
}
