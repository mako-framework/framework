<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\syringe\Container;

/**
 * Command dispatcher.
 *
 * @author  Frederic G. Østby
 */
class Dispatcher
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\syringe\Container  $container  Container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolves the command.
	 *
	 * @access  protected
	 * @param   string                 $command  Command class
	 * @return  \mako\reactor\Command
	 */
	protected function resolve(string $command): Command
	{
		return $this->container->get($command);
	}

	/**
	 * Checks for invalid arguments or options.
	 *
	 * @access  protected
	 * @param   \mako\reactor\Command  $command            Command arguments
	 * @param   array                  $providedArguments  Provided arguments
	 */
	protected function checkForInvalidArguments(Command $command, array $providedArguments)
	{
		$commandArguments = array_keys($command->getCommandArguments() + $command->getCommandOptions());

		foreach(array_keys($providedArguments) as $name)
		{
			if(!in_array($name, ['arg0', 'arg1']) && !in_array($name, $commandArguments))
			{
				if(strpos($name, 'arg') === 0)
				{
					$type      = 'argument';
					$exception = InvalidArgumentException::class;
				}
				else
				{
					$type      = 'option';
					$exception = InvalidOptionException::class;
				}

				throw new $exception(vsprintf("%s(): Invalid %s [ %s ].", [__METHOD__, $type, $name]), $name);
			}
		}
	}

	/**
	 * Checks for missing required arguments or options.
	 *
	 * @access  protected
	 * @param   array   $commandArguments   Command arguments
	 * @param   array   $providedArguments  Provided arguments
	 * @param   string  $exception          Exception to throw
	 */
	protected function checkForMissingArgumentsOrOptions(array $commandArguments, array $providedArguments, string $exception)
	{
		$providedArguments = array_keys($providedArguments);

		foreach($commandArguments as $name => $details)
		{
			if(isset($details['optional']) && $details['optional'] === false && !in_array($name, $providedArguments))
			{
				$type = $exception === MissingArgumentException::class ? 'argument' : 'option';

				throw new $exception(vsprintf("%s(): Missing required %s [ %s ].", [__METHOD__, $type, $name]), $name);
			}
		}
	}

	/**
	 * Checks for missing required arguments.
	 *
	 * @access  protected
	 * @param   \mako\reactor\Command  $command            Command instance
	 * @param   array                  $providedArguments  Provided arguments
	 */
	protected function checkForMissingArguments(Command $command, array $providedArguments)
	{
		$this->checkForMissingArgumentsOrOptions($command->getCommandArguments(), $providedArguments, MissingArgumentException::class);
	}

	/**
	 * Checks for missing required options.
	 *
	 * @access  protected
	 * @param   \mako\reactor\Command  $command            Command instance
	 * @param   array                  $providedArguments  Provided arguments
	 */
	protected function checkForMissingOptions(Command $command, array $providedArguments)
	{
		$this->checkForMissingArgumentsOrOptions($command->getCommandOptions(), $providedArguments, MissingOptionException::class);
	}

	/**
	 * Checks arguments and options.
	 *
	 * @param  \mako\reactor\Command  $command            Command instance
	 * @param  array                  $providedArguments  Provided arguments
	 */
	protected function checkArgumentsAndOptions(Command $command, array $providedArguments)
	{
		if($command->isStrict())
		{
			$this->checkForInvalidArguments($command, $providedArguments);
		}

		$this->checkForMissingArguments($command, $providedArguments);

		$this->checkForMissingOptions($command, $providedArguments);
	}

	/**
	 * Executes the command.
	 *
	 * @access  protected
	 * @param   \mako\reactor\Command  $command    Command instance
	 * @param   array                  $arguments  Command arguments
	 */
	protected function execute(Command $command, array $arguments)
	{
		$this->container->call([$command, 'execute'], $arguments);
	}

	/**
	 * Dispatches the command.
	 *
	 * @access  public
	 * @param   string  $command    Command class
	 * @param   array   $arguments  Command arguments
	 */
	public function dispatch(string $command, array $arguments)
	{
		$command = $this->resolve($command);

		if($command->shouldExecute())
		{
			$this->checkArgumentsAndOptions($command, $arguments);

			$this->execute($command, $arguments);
		}
	}
}