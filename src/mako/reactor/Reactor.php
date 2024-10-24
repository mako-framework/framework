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
use mako\reactor\attributes\Arguments;
use mako\reactor\attributes\CommandDescription;
use mako\syringe\Container;
use ReflectionClass;

use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_keys;
use function array_map;
use function implode;
use function ksort;

/**
 * Reactor.
 */
class Reactor
{
	use SuggestionTrait;

	/**
	 * Dispatcher.
	 */
	protected Dispatcher $dispatcher;

	/**
	 * Commands.
	 */
	protected array $commands = [];

	/**
	 * Logo.
	 */
	protected null|string $logo = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Input $input,
		protected Output $output,
		protected Container $container = new Container,
		?Dispatcher $dispatcher = null
	) {
		$this->dispatcher = $dispatcher ?? new Dispatcher($this->container);
	}

	/**
	 * Returns the input.
	 */
	public function getInput(): Input
	{
		return $this->input;
	}

	/**
	 * Returns the output.
	 */
	public function getOutput(): Output
	{
		return $this->output;
	}

	/**
	 * Registers a command.
	 */
	public function registerCommand(string $command, string $class): void
	{
		$this->commands[$command] = $class;
	}

	/**
	 * Sets the reactor logo.
	 */
	public function setLogo(string $logo): void
	{
		$this->logo = $logo;
	}

	/**
	 * Setup the reactor.
	 */
	protected function setup(): void
	{
		$arguments = $this->input->getArgumentParser();

		// Register default reactor arguments

		$arguments->addArguments([
			new Argument('command', 'Command name', Argument::IS_OPTIONAL),
			new Argument('--help', 'Displays helpful information', Argument::IS_BOOL),
			new Argument('--mute', 'Mutes all output', Argument::IS_BOOL),
		]);

		// Preparse arguments and ignore unknown arguments so that we
		// don't trigger any errors before the command arguments get registered

		$arguments->parse(true);
	}

	/**
	 * Draws information table.
	 */
	protected function drawTable(string $heading, array $headers, array $rows): void
	{
		if (!empty($rows)) {
			$this->output->write(PHP_EOL);

			$this->output->writeLn("<yellow>{$heading}</yellow>");

			$this->output->write(PHP_EOL);

			$table = new Table($this->output);

			$headers = array_map(static fn ($value) => "<green>{$value}</green>", $headers);

			$table->draw($headers, $rows);
		}
	}

	/**
	 * Draws an argument table.
	 */
	protected function drawArgumentTable(string $heading, array $arguments): void
	{
		$argInfo = [];

		foreach ($arguments as $argument) {
			$argInfo[] = [
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

		if ($this->logo !== null) {
			$this->output->writeLn($this->logo);

			$this->output->write(PHP_EOL);
		}

		$this->output->writeLn('<yellow>Usage:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn('php reactor [command] [arguments] [options]');

		// Display global arguments and options if there are any

		$arguments = $this->input->getArgumentParser()->getArguments();

		ksort($arguments);

		$this->drawArgumentTable('Global arguments and options:', $arguments);
	}

	/**
	 * Returns the command description.
	 */
	protected function getCommandDescription(ReflectionClass $class): string
	{
		$attributes = $class->getAttributes(CommandDescription::class);

		if (empty($attributes)) {
			/** @var \mako\reactor\CommandInterface $command */
			$command = $class->newInstanceWithoutConstructor();

			return $command->getDescription();
		}

		return $attributes[0]->newInstance()->getDescription();
	}

	/**
	 * Returns the command additional information.
	 */
	protected function getCommandAdditionalInformation(ReflectionClass $class): string
	{
		$attributes = $class->getAttributes(CommandDescription::class);

		if (empty($attributes)) {
			return '';
		}

		return $attributes[0]->newInstance()->getAdditionalInformation();
	}

	/**
	 * Returns the command arguments.
	 */
	public function getCommandArguments(ReflectionClass $class): array
	{
		$attributes = $class->getAttributes(Arguments::class);

		if (empty($attributes)) {
			/** @var \mako\reactor\CommandInterface $command */
			$command = $class->newInstanceWithoutConstructor();

			return $command->getArguments();
		}

		return $attributes[0]->newInstance()->getArguments();
	}

	/**
	 * Returns an array of command information.
	 */
	protected function getCommands(): array
	{
		$info = [];

		foreach ($this->commands as $name => $class) {
			$info[$name] = [$name, $this->getCommandDescription(new ReflectionClass($class))];
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
	 */
	protected function displayReactorInfoAndCommandList(): int
	{
		$this->displayReactorInfo();

		$this->listCommands();

		return CommandInterface::STATUS_SUCCESS;
	}

	/**
	 * Returns TRUE if the command exists and FALSE if not.
	 */
	protected function commandExists(string $command): bool
	{
		return isset($this->commands[$command]);
	}

	/**
	 * Displays error message for unknown commands.
	 */
	protected function unknownCommand(string $command): int
	{
		$message = "Unknown command [ {$command} ].";

		if (($suggestion = $this->suggest($command, array_keys($this->commands))) !== null) {
			$message .= " Did you mean [ {$suggestion} ]?";
		}

		$this->output->writeLn("<red>{$message}</red>");

		$this->listCommands();

		return CommandInterface::STATUS_ERROR;
	}

	/**
	 * Displays information about the chosen command.
	 */
	protected function displayCommandHelp(string $command): int
	{
		$commandReflection = new ReflectionClass($this->commands[$command]);

		$description = $this->getCommandDescription($commandReflection);

		$this->output->writeLn('<yellow>Command:</yellow>');

		$this->output->write(PHP_EOL);

		$this->output->writeLn("php reactor {$command}");

		if (!empty($description)) {
			$this->output->write(PHP_EOL);

			$this->output->writeLn('<yellow>Description:</yellow>');

			$this->output->write(PHP_EOL);

			$this->output->writeLn($description);

			$additionalInformation = $this->getCommandAdditionalInformation($commandReflection);

			if (!empty($additionalInformation)) {
				$this->output->write(PHP_EOL);

				$this->output->writeLn($additionalInformation);
			}
		}

		$this->drawArgumentTable('Arguments and options:', $this->getCommandArguments($commandReflection));

		return CommandInterface::STATUS_SUCCESS;
	}

	/**
	 * Registers the command arguments and dispatches the command.
	 */
	protected function registerCommandArgumentsAndDispatch(string $command): int
	{
		$commandReflection = new ReflectionClass($this->commands[$command]);

		$globalArgumentNames = array_keys($this->input->getArgumentParser()->getArguments());

		$this->input->getArgumentParser()->clearCache()->addArguments($this->getCommandArguments($commandReflection));

		$filteredArguments = array_diff_key($this->input->getArguments(), array_flip($globalArgumentNames));

		return $this->dispatcher->dispatch($this->commands[$command], $filteredArguments);
	}

	/**
	 * Run the reactor.
	 */
	public function run(): int
	{
		try {
			$this->setup();

			if ($this->input->getArgument('--mute') === true) {
				$this->output->mute();
			}

			if (($command = $this->input->getArgument('command')) === null) {
				return $this->displayReactorInfoAndCommandList();
			}

			if ($this->commandExists($command) === false) {
				return $this->unknownCommand($command);
			}

			if ($this->input->getArgument('--help') === true) {
				return $this->displayCommandHelp($command);
			}

			return $this->registerCommandArgumentsAndDispatch($command);
		}
		catch (ArgumentException | UnexpectedValueException $e) {
			$this->output->errorLn("<red>{$e->getMessage()}</red>");

			return CommandInterface::STATUS_ERROR;
		}
	}
}
