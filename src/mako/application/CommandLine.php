<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application;

use mako\application\Application;
use mako\config\Config;
use mako\reactor\Reactor;

/**
 * Web application.
 *
 * @author  Frederic G. Østby
 */

class CommandLine extends Application
{
	/**
	 * Returns the route collection.
	 * 
	 * @access  public
	 * @return  \mako\http\routing\Routes
	 */

	public function getRouteCollection()
	{
		return $this->loadRoutes();
	}

	/**
	 * Returns all registered commands.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function getCommands()
	{
		// Define core commands

		$commands = 
		[
			'app.generate_secret' => 'mako\application\commands\app\GenerateSecret',
			'app.routes'          => 'mako\application\commands\app\ListRoutes',
			'migrate.create'      => 'mako\application\commands\migrations\Create',
			'migrate.status'      => 'mako\application\commands\migrations\Status',
			'migrate.up'          => 'mako\application\commands\migrations\Up',
			'migrate.down'        => 'mako\application\commands\migrations\Down',
			'migrate.reset'       => 'mako\application\commands\migrations\Reset',
			'server'              => 'mako\application\commands\server\Server',
		];

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
		
		$input = $this->container->get('input');

		$output = $this->container->get('output');

		// Create reactor and register custom options

		$reactor = new Reactor($input, $output, $this->container);

		$reactor->registerCustomOption('env', 'Overrides the Mako environment', function(Config $config, $option)
		{
			putenv('MAKO_ENV=' . $option);

			$config->setEnvironment($option);
		});

		$reactor->registerCustomOption('database', 'Overrides the default database connection', function(Config $config, $option)
		{
			$config->set('database.default', $option);
		});

		// Register reactor commands
		
		foreach($this->getCommands() as $command => $class)
		{
			$reactor->registerCommand($command, $class);
		}

		// Run the reactor

		$reactor->run();
	}
}