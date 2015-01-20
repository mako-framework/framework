<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use Closure;

use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\cli\output\helpers\Table;
use mako\reactor\Dispatcher;
use mako\syringe\Container;

/**
 * Reactor.
 * 
 * @author  Frederic G. Østby
 */

class Reactor
{
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
	 * @access  public
	 * @param   \mako\cli\input\Input     $input       Input
	 * @param   \mako\cli\output\Output   $output      Output
	 * @param   \mako\syringe\Container   $container   Container
	 * @param   \mako\reactor\Dispatcher  $dispatcher  Command dispatcher
	 */

	public function __construct(Input $input, Output $output, Container $container = null, Dispatcher $dispatcher = null)
	{
		$this->input = $input;

		$this->output = $output;

		$this->container = $container ?: new Container;

		$this->dispatcher = $dispatcher ?: new Dispatcher($this->container);
	}

	/**
	 * Registers a command.
	 * 
	 * @access  public
	 * @param   string  $command  Command
	 * @param   string  $class    Command class
	 */

	public function registerCommand($command, $class)
	{
		$this->commands[$command] = $class;
	}

	/**
	 * Register a custom reactor option.
	 * 
	 * @access  public
	 * @param   string    $name         Option name
	 * @param   string    $description  Option description
	 * @param   \Closure  $handler      Option handler
	 */

	public function registerCustomOption($name, $description, Closure $handler)
	{
		$this->options[$name] = ['description' => $description, 'handler' => $handler];
	}

	/**
	 * Sets the reactor logo.
	 * 
	 * @access  public
	 * @param   string  $logo  ASCII logo
	 */

	public function setLogo($logo)
	{
		$this->logo = $logo;
	}

	/**
	 * Handles custom reactor options.
	 * 
	 * @access  protected
	 */

	protected function handleCustomOptions()
	{
		foreach($this->options as $name => $option)
		{
			$input = $this->input->getArgument($name);

			if(!empty($input))
			{
				$handler = $option['handler'];

				$this->container->call($handler, ['option' => $input]);
			}
		}
	}

	/**
	 * Returns an array of option information.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function getOptions()
	{
		$options = [['--mute', 'Disables all output']];

		foreach($this->options as $name => $option)
		{
			$options[] = ['--' . $name, $option['description']];
		}

		return $options;
	}

	/**
	 * Displays basic reactor information.
	 * 
	 * @access  protected
	 */

	protected function displayReactorInfo()
	{
		if(!empty($this->logo))
		{
			$this->output->writeLn($this->logo);

			$this->output->write(PHP_EOL);
		}

		$this->output->writeLn("<yellow>Usage:</yellow>");

		$this->output->write(PHP_EOL);

		$this->output->writeLn("php reactor [command] [arguments] [options]");

		$this->output->write(PHP_EOL);

		$this->output->writeLn("<yellow>Global options:</yellow>");

		$this->output->write(PHP_EOL);

		$table = new Table($this->output);

		$headers = 
		[
			'<green>Option</green>', 
			'<green>Description</green>'
		];

		$options = $this->getOptions();

		$table->draw($headers, $options);

		$this->output->write(PHP_EOL);
	}

	/**
	 * Returns an array of command information.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function getCommands()
	{
		$info = [];

		foreach($this->commands as $name => $class)
		{
			$info[$name] = [$name, $this->container->get($class)->getCommandDescription()];
		}

		ksort($info);

		return $info;
	}

	/**
	 * Lists available commands.
	 * 
	 * @access  protected
	 */

	protected function listCommands()
	{
		$this->output->writeLn("<yellow>Available commands:</yellow>");

		$this->output->write(PHP_EOL);

		$table = new Table($this->output);

		$headers = 
		[
			'<green>Command</green>', 
			'<green>Description</green>'
		];

		$commands = $this->getCommands();

		$table->draw($headers, $commands);
	}

	/**
	 * Dispatches a command.
	 * 
	 * @access  protected
	 * @param   string     $command  Command
	 */

	protected function dispatch($command)
	{
		if(!isset($this->commands[$command]))
		{
			$this->output->writeLn('<red>Unknown command [ ' . $command . ' ].</red>');

			$this->output->write(PHP_EOL);

			$this->listCommands();

			return;
		}

		$this->dispatcher->dispatch($this->commands[$command], $this->input->getArguments());
	}

	/**
	 * Run the reactor.
	 * 
	 * @access  public
	 */

	public function run()
	{
		if($this->input->getArgument('mute', false) === true)
		{
			$this->output->mute();
		}

		$this->handleCustomOptions();

		$this->output->write(PHP_EOL);

		if(($command = $this->input->getArgument(1)) === null)
		{
			$this->displayReactorInfo();

			$this->listCommands();
		}
		else
		{
			$this->dispatch($command);
		}

		$this->output->write(PHP_EOL);
	}
}