<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli;

use mako\application\Application as BaseApplication;
use mako\application\cli\commands\app\GenerateKey;
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
use mako\http\routing\Routes;
use mako\Mako;
use mako\reactor\CommandInterface;
use mako\reactor\Reactor;

use function array_merge;
use function array_shift;
use function file_get_contents;
use function ob_start;
use function putenv;
use function str_replace;

/**
 * CLI application.
 *
 * @author Frederic G. Østby
 */
class Application extends BaseApplication
{
	/**
	 * Reactor instance.
	 *
	 * @var \mako\reactor\Reactor
	 */
	protected $reactor;

	/**
	 * Creates a input instance.
	 *
	 * @return \mako\cli\input\Input
	 */
	protected function inputFactory(): Input
	{
		$argv = $_SERVER['argv'];

		array_shift($argv); // Remove the script name

		return new Input(new Reader, new ArgvParser($argv));
	}

	/**
	 * Creates an output instance.
	 *
	 * @return \mako\cli\output\Output
	 */
	protected function outputFactory(): Output
	{
		return new Output(new Standard, new Error, new Formatter);
	}

	/**
	 * Creates a reactor instance.
	 *
	 * @return \mako\reactor\Reactor
	 */
	protected function reactorFactory(): Reactor
	{
		return new Reactor($this->container->get(Input::class), $this->container->get(Output::class), $this->container);
	}

	/**
	 * Loads the reactor ASCII logo.
	 *
	 * @return string
	 */
	protected function loadLogo(): string
	{
		$logo = file_get_contents(__DIR__ . '/resources/logo.txt');

		return str_replace('{version}', Mako::VERSION, $logo);
	}

	/**
	 * Register and handle global arguments.
	 *
	 * @return void
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
		$this->container->registerSingleton([Input::class, 'input'], function()
		{
			return $this->inputFactory();
		});

		$this->container->registerSingleton([Output::class, 'output'], function()
		{
			return $this->outputFactory();
		});

		$this->reactor = $this->reactorFactory();

		// Set logo

		$this->reactor->setLogo($this->loadLogo());

		// Register and handle global arguments

		$this->registerAndhandleGlobalArguments();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function initialize(): void
	{
		parent::initialize();

		$this->startReactor();
	}

	/**
	 * Returns all registered commands.
	 *
	 * @return array
	 */
	protected function getCommands(): array
	{
		// Define core commands

		$commands =
		[
			'app.generate_key'    => GenerateKey::class,
			'app.generate_secret' => GenerateSecret::class,
		];

		if($this->container->has(Routes::class))
		{
			$commands = array_merge($commands,
			[
				'app.routes' => ListRoutes::class,
				'server'     => Server::class,
			]);
		}

		if($this->container->has(CacheManager::class))
		{
			$commands = array_merge($commands,
			[
				'cache.remove' => Remove::class,
				'cache.clear'  => Clear::class,
			]);
		}

		if($this->container->has(DatabaseConnectionManager::class))
		{
			$commands = array_merge($commands,
			[
				'migrate.create' => Create::class,
				'migrate.status' => Status::class,
				'migrate.up'     => Up::class,
				'migrate.down'   => Down::class,
				'migrate.reset'  => Reset::class,
			]);
		}

		// Add application commands

		$commands += $this->config->get('application.commands');

		// Add package commands

		foreach($this->packages as $package)
		{
			$commands += $package->getCommands();
		}

		// Return commands

		return $commands;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(): void
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
