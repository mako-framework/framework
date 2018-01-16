<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli;

use mako\Mako;
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
use mako\cli\input\Input;
use mako\cli\input\reader\Reader;
use mako\cli\output\Output;
use mako\cli\output\formatter\Formatter;
use mako\cli\output\writer\Error;
use mako\cli\output\writer\Standard;
use mako\config\Config;
use mako\reactor\Reactor;

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
		return new Input(new Reader);
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
		return new Reactor($this->container->get('input'), $this->container->get('output'), $this->container);
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
	 * Registers global reactor options.
	 */
	protected function registerGlobalReactorOptions()
	{
		$this->reactor->registerGlobalOption('env', 'Overrides the Mako environment', function(Config $config, $option)
		{
			putenv('MAKO_ENV=' . $option);

			$config->setEnvironment($option);
		}, 'init');

		$this->reactor->registerGlobalOption('mute', 'Mutes all output', function(Output $output)
		{
			$output->mute();
		}, 'init');

		$this->reactor->registerGlobalOption('database', 'Overrides the default database connection', function(Config $config, $option)
		{
			$config->set('database.default', $option);
		});
	}

	/**
	 * Starts the reactor.
	 */
	protected function startReactor()
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

		// Register global options

		$this->registerGlobalReactorOptions();

		// Handle initialization options

		$this->reactor->handleGlobalOptions('init');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function initialize()
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

		if($this->container->has('routes'))
		{
			$commands = array_merge($commands,
			[
				'app.routes' => ListRoutes::class,
				'server'     => Server::class,
			]);
		}

		if($this->container->has('cache'))
		{
			$commands = array_merge($commands,
			[
				'cache.remove' => Remove::class,
				'cache.clear'  => Clear::class,
			]);
		}

		if($this->container->has('database'))
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
	public function run()
	{
		ob_start();

		// Register reactor commands

		foreach($this->getCommands() as $command => $class)
		{
			$this->reactor->registerCommand($command, $class);
		}

		// Run the reactor

		$exitCode = $this->reactor->run();

		exit($exitCode);
	}
}
