<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli;

use mako\application\Application as BaseApplication;
use mako\application\cli\commands\app\GenerateKey;
use mako\application\cli\commands\app\GeneratePreloader;
use mako\application\cli\commands\app\GenerateSecret;
use mako\application\cli\commands\app\ListRoutes;
use mako\application\cli\commands\cache\Clear;
use mako\application\cli\commands\cache\Remove;
use mako\application\cli\commands\migrations\Create;
use mako\application\cli\commands\migrations\Down;
use mako\application\cli\commands\migrations\Reset;
use mako\application\cli\commands\migrations\Status;
use mako\application\cli\commands\migrations\Up;
use mako\application\cli\commands\server\Server;
use mako\cache\CacheManager;
use mako\classes\ClassFinder;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\ArgvParser;
use mako\cli\input\arguments\exceptions\ArgumentException;
use mako\cli\input\arguments\exceptions\UnexpectedValueException;
use mako\cli\input\Input;
use mako\cli\input\reader\Reader;
use mako\cli\output\formatter\Formatter;
use mako\cli\output\Output;
use mako\cli\output\writer\Error;
use mako\cli\output\writer\Standard;
use mako\database\ConnectionManager as DatabaseConnectionManager;
use mako\file\Finder;
use mako\http\routing\Routes;
use mako\Mako;
use mako\reactor\CommandInterface;
use mako\reactor\Reactor;
use ReflectionClass;

use function array_shift;
use function file_get_contents;
use function ob_start;
use function putenv;
use function str_replace;

/**
 * CLI application.
 */
class Application extends BaseApplication
{
	/**
	 * Reactor instance.
	 */
	protected Reactor $reactor;

	/**
	 * Creates a input instance.
	 */
	protected function inputFactory(): Input
	{
		$argv = $_SERVER['argv'];

		array_shift($argv); // Remove the script name

		return new Input(new Reader, new ArgvParser($argv));
	}

	/**
	 * Creates an output instance.
	 */
	protected function outputFactory(): Output
	{
		return new Output(new Standard, new Error, new Formatter);
	}

	/**
	 * Creates a reactor instance.
	 */
	protected function reactorFactory(): Reactor
	{
		return new Reactor($this->container->get(Input::class), $this->container->get(Output::class), $this->container);
	}

	/**
	 * Loads the reactor ASCII logo.
	 */
	protected function loadLogo(): string
	{
		$logo = file_get_contents(__DIR__ . '/resources/logo.txt');

		return str_replace('{version}', Mako::VERSION, $logo);
	}

	/**
	 * Register and handle global arguments.
	 */
	protected function registerAndhandleGlobalArguments(): void
	{
		$arguments = $this->reactor->getInput()->getArgumentParser();

		// Register global arguments

		$arguments->addArguments
		([
			new Argument('--env', 'Overrides the Mako environment', Argument::IS_OPTIONAL),
		]);

		// Get arguments

		try
		{
			$arguments = $arguments->parse(true);
		}
		catch(ArgumentException | UnexpectedValueException $e)
		{
			$this->reactor->getOutput()->errorLn("<red>{$e->getMessage()}</red>");

			exit(CommandInterface::STATUS_ERROR);
		}

		// Set the environment if we got one

		if($arguments['env'] !== null)
		{
			putenv("MAKO_ENV={$arguments['env']}");

			$this->config->setEnvironment($arguments['env']);
		}
	}

	/**
	 * Starts the reactor.
	 */
	protected function startReactor(): void
	{
		$this->container->registerSingleton([Input::class, 'input'], fn () => $this->inputFactory());

		$this->container->registerSingleton([Output::class, 'output'], fn () => $this->outputFactory());

		$this->reactor = $this->reactorFactory();

		// Set logo

		$this->reactor->setLogo($this->loadLogo());

		// Register and handle global arguments

		$this->registerAndhandleGlobalArguments();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function initialize(): void
	{
		parent::initialize();

		$this->startReactor();
	}

	/**
	 * Returns the application commands.
	 */
	protected function getApplicationCommands(): array
	{
		$commands = [];

		$commandsDirectory = $this->config->get('application.commands_directory');

		if($commandsDirectory !== null)
		{
			$finder = new ClassFinder(new Finder((array) $commandsDirectory));

			$finder->excludeInterfaces()->excludeAbstractClasses()->excludeTraits();

			foreach($finder->findImplementing(CommandInterface::class) as $commandClass)
			{
				/** @var \mako\reactor\CommandInterface $command */
				$command = (new ReflectionClass($commandClass))->newInstanceWithoutConstructor();

				$command = $command->getCommand();

				if($command !== null)
				{
					$commands[$command] = $commandClass;
				}
			}
		}

		return $this->config->get('application.commands', []) + $commands;
	}

	/**
	 * Returns all registered commands.
	 */
	protected function getCommands(): array
	{
		// Define core commands

		$commands =
		[
			'app:generate-key'       => GenerateKey::class,
			'app:generate-secret'    => GenerateSecret::class,
			'app:generate-preloader' => GeneratePreloader::class,
		];

		if($this->container->has(Routes::class))
		{
			$commands = [...$commands, ...
			[
				'app:routes' => ListRoutes::class,
				'app:server' => Server::class,
			]];
		}

		if($this->container->has(CacheManager::class))
		{
			$commands = [...$commands, ...
			[
				'cache:remove' => Remove::class,
				'cache:clear'  => Clear::class,
			]];
		}

		if($this->container->has(DatabaseConnectionManager::class))
		{
			$commands = [...$commands, ...
			[
				'migration:create' => Create::class,
				'migration:status' => Status::class,
				'migration:up'     => Up::class,
				'migration:down'   => Down::class,
				'migration:reset'  => Reset::class,
			]];
		}

		// Add application commands

		$commands += $this->getApplicationCommands();

		// Add package commands

		foreach($this->packages as $package)
		{
			$commands += $package->getCommands();
		}

		// Return commands

		return $commands;
	}

	/**
	 * {@inheritDoc}
	 */
	public function run(): never
	{
		ob_start();

		// Register reactor commands

		foreach($this->getCommands() as $command => $class)
		{
			$this->reactor->registerCommand($command, $class);
		}

		// Run the reactor

		exit($this->reactor->run());
	}
}
