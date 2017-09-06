<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use Closure;

use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\cli\output\helpers\Table;
use mako\reactor\Command;
use mako\reactor\Dispatcher;
use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\reactor\traits\SuggestionTrait;
use mako\syringe\Container;

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
	public function __construct(Input $input, Output $output, Container $container = null, Dispatcher $dispatcher = null)
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
	public function registerCommand(string $command, string $class)
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
	public function registerGlobalOption(string $name, string $description, Closure $handler, string $group = 'default')
	{
		$this->options[$group][$name] = ['description' => $description, 'handler' => $handler];
	}

	/**
	 * Sets the reactor logo.
	 *
	 * @param string $logo ASCII logo
	 */
	public function setLogo(string $logo)
	{
		$this->logo = $logo;
	}

	/**
	 * Handles global reactor options.
	 *
	 * @param string $group Option group
	 */
	public function handleGlobalOptions(string $group = 'default')
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

					$this->input->removeArgument($name);
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
	protected function drawTable(string $heading, array $headers, array $rows)
	{
		if(!empty($rows))
		{
			$this->output->write(PHP_EOL);

			$this->output->writeLn('<yellow>' . $heading . '</yellow>');

			$this->output->write(PHP_EOL);

			$table = new Table($this->output);

			$headers = array_map(function($value){ return '<green>' . $value . '</green>'; }, $headers);

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
				$options[] = ['--' . $name, $option['description']];
			}
		}

		sort($options);

		return $options;
	}

	/**
	 * Displays reactor options of there are any.
	 */
	protected function listOptions()
	{
		$options = $this->getOptions();

		$this->drawTable('Global options:', ['Option', 'Description'], $options);
	}

	/**
	 * Displays basic reactor information.
	 */
	protected function displayReactorInfo()
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
	 * Returns an array of command information.
	 *
	 * @return array
	 */
	protected function getCommands(): array
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
	 * Lists available commands if there are any.
	 */
	protected function listCommands()
	{
		$commands = $this->getCommands();

		$this->drawTable('Available commands:', ['Command', 'Description'], $commands);
	}

	/**
	 * Dispatches a command.
	 *
	 * @param  string $command Command
	 * @return int
	 */
	protected function dispatch(string $command): int
	{
		if(!isset($this->commands[$command]))
		{
			$message = 'Unknown command [ ' . $command . ' ].';

			if(($suggestion = $this->suggest($command, array_keys($this->commands))) !== null)
			{
				$message .= ' Did you mean [ ' . $suggestion . ' ]?';
			}

			$this->output->writeLn('<red>' . $message . '</red>');

			$this->listCommands();

			return Command::STATUS_ERROR;
		}

		return $this->dispatcher->dispatch($this->commands[$command], $this->input->getArguments());
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
			$this->displayReactorInfo();

			$this->listCommands();

			return Command::STATUS_SUCCESS;
		}

		try
		{
			$exitCode = $this->dispatch($command);
		}
		catch(InvalidOptionException $e)
		{
			$message = 'Invalid option [ ' . $e->getName() . ' ].';

			if(($suggestion = $e->getSuggestion()) !== null)
			{
				$message .= ' Did you mean [ ' . $suggestion . ' ]?';
			}

			$this->output->errorLn('<red>' . $message . '</red>');
		}
		catch(InvalidArgumentException $e)
		{
			$this->output->errorLn('<red>Invalid argument [ ' . $e->getName() . ' ].</red>');
		}
		catch(MissingOptionException $e)
		{
			$this->output->errorLn('<red>Missing required option [ ' . $e->getName() . ' ].</red>');
		}
		catch(MissingArgumentException $e)
		{
			$this->output->errorLn('<red>Missing required argument [ ' . $e->getName() . ' ].</red>');
		}

		return $exitCode ?? Command::STATUS_ERROR;
	}
}
