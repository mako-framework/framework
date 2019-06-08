<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\reactor\exceptions\InvalidArgumentException;
use mako\reactor\exceptions\InvalidOptionException;
use mako\reactor\exceptions\MissingArgumentException;
use mako\reactor\exceptions\MissingOptionException;
use mako\reactor\traits\SuggestionTrait;
use mako\syringe\Container;
use mako\utility\Str;

use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function in_array;
use function is_int;
use function str_replace;
use function strpos;
use function vsprintf;

/**
 * Command dispatcher.
 *
 * @author Frederic G. Østby
 */
class Dispatcher
{
	use SuggestionTrait;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \mako\syringe\Container $container Container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolves the command.
	 *
	 * @param  string                         $command Command class
	 * @return \mako\reactor\CommandInterface
	 */
	protected function resolve(string $command): CommandInterface
	{
		return $this->container->get($command);
	}

	/**
	 * Checks for invalid arguments or options.
	 *
	 * @param  \mako\reactor\CommandInterface                    $command           Command arguments
	 * @param  array                                             $providedArguments Provided arguments
	 * @param  array                                             $globalOptions     Global options
	 * @throws \mako\reactor\exceptions\InvalidArgumentException
	 * @throws \mako\reactor\exceptions\InvalidOptionException
	 */
	protected function checkForInvalidArguments(CommandInterface $command, array $providedArguments, array $globalOptions): void
	{
		$commandArguments = array_keys($command->getCommandArguments() + $command->getCommandOptions());

		$defaultAndGlobalOptions = array_merge(['arg0', 'arg1'], $globalOptions);

		$shorthandOptions = array_column($command->getCommandOptions(), 'shorthand');

		foreach(array_keys($providedArguments) as $name)
		{
			if(!in_array($name, $defaultAndGlobalOptions) && !in_array($name, $commandArguments) && !in_array($name, $shorthandOptions))
			{
				if(strpos($name, 'arg') === 0)
				{
					throw new InvalidArgumentException(vsprintf('Invalid argument [ %s ].', [$name]), $name);
				}

				if(strlen($name) === 1)
				{
					throw new InvalidOptionException(vsprintf('Invalid shorthand option [ %s ].', [$name]), $name, $this->suggest($name, $shorthandOptions));
				}

				throw new InvalidOptionException(vsprintf('Invalid option [ %s ].', [$name]), $name, $this->suggest($name, $commandArguments));
			}
		}
	}

	/**
	 * Checks for missing required arguments or options.
	 *
	 * @param  array                                      $commandArguments  Command arguments
	 * @param  array                                      $providedArguments Provided arguments
	 * @param  string                                     $exception         Exception to throw
	 * @throws \mako\reactor\exceptions\ArgumentException
	 */
	protected function checkForMissingArgumentsOrOptions(array $commandArguments, array $providedArguments, string $exception): void
	{
		$providedArguments = array_keys($providedArguments);

		foreach($commandArguments as $name => $details)
		{
			if(isset($details['optional']) && $details['optional'] === false && !in_array($name, $providedArguments))
			{
				$type = $exception === MissingArgumentException::class ? 'argument' : 'option';

				throw new $exception(vsprintf('Missing required %s [ %s ].', [$type, $name]), $name);
			}
		}
	}

	/**
	 * Checks for missing required arguments.
	 *
	 * @param \mako\reactor\CommandInterface $command           Command instance
	 * @param array                          $providedArguments Provided arguments
	 */
	protected function checkForMissingArguments(CommandInterface $command, array $providedArguments): void
	{
		$this->checkForMissingArgumentsOrOptions($command->getCommandArguments(), $providedArguments, MissingArgumentException::class);
	}

	/**
	 * Checks for missing required options.
	 *
	 * @param \mako\reactor\CommandInterface $command           Command instance
	 * @param array                          $providedArguments Provided arguments
	 */
	protected function checkForMissingOptions(CommandInterface $command, array $providedArguments): void
	{
		$this->checkForMissingArgumentsOrOptions($command->getCommandOptions(), $providedArguments, MissingOptionException::class);
	}

	/**
	 * Checks arguments and options.
	 *
	 * @param \mako\reactor\CommandInterface $command           Command instance
	 * @param array                          $providedArguments Provided arguments
	 * @param array                          $globalOptions     Global options
	 */
	protected function checkArgumentsAndOptions(CommandInterface $command, array $providedArguments, array $globalOptions): void
	{
		if($command->isStrict())
		{
			$this->checkForInvalidArguments($command, $providedArguments, $globalOptions);
		}

		$this->checkForMissingArguments($command, $providedArguments);

		$this->checkForMissingOptions($command, $providedArguments);
	}

	/**
	 * Converts arguments from shorthand form to full form, and full form to camel case.
	 *
	 * @param  array $options   Options
	 * @param  array $arguments Arguments
	 * @return array
	 */
	protected function convertArguments(array $options, array $arguments): array
	{
		$shorthandOptions =  [];

		foreach($options as $key => $option)
		{
			if(array_key_exists('shorthand', $option))
			{
				$shorthandOptions[$option['shorthand']] = $key;
			}
		}

		return array_combine(array_map(function($key) use ($shorthandOptions)
		{
			if(strlen($key) === 1 && count($shorthandOptions))
			{
				$key = $shorthandOptions[$key];
			}

			return Str::underscored2camel(str_replace('-', '_', $key));
		}, array_keys($arguments)), array_values($arguments));
	}

	/**
	 * Executes the command.
	 *
	 * @param  \mako\reactor\CommandInterface $command   Command instance
	 * @param  array                          $arguments Command arguments
	 * @return mixed
	 */
	protected function execute(CommandInterface $command, array $arguments)
	{
		return $this->container->call([$command, 'execute'], $this->convertArguments($command->getCommandOptions(), $arguments));
	}

	/**
	 * Dispatches the command.
	 *
	 * @param  string $command       Command class
	 * @param  array  $arguments     Command arguments
	 * @param  array  $globalOptions Global options
	 * @return int
	 */
	public function dispatch(string $command, array $arguments, array $globalOptions): int
	{
		$command = $this->resolve($command);

		$this->checkArgumentsAndOptions($command, $arguments, $globalOptions);

		$returnValue = $this->execute($command, $arguments);

		return is_int($returnValue) ? $returnValue : CommandInterface::STATUS_SUCCESS;
	}
}
