<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\reactor;

use mako\syringe\Container;

use function array_filter;
use function is_int;

/**
 * Command dispatcher.
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
	 * Returns arguments where null values have been removed.
	 *
	 * @param  array $arguments
	 * @return array
	 */
	protected function filterArguments(array $arguments): array
	{
		return array_filter($arguments, static function($value)
		{
			return $value !== null;
		});
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
		return $this->container->call([$command, 'execute'], $arguments);
	}

	/**
	 * Dispatches the command.
	 *
	 * @param  string $command   Command class
	 * @param  array  $arguments Command arguments
	 * @return int
	 */
	public function dispatch(string $command, array $arguments): int
	{
		$returnValue = $this->execute($this->resolve($command), $this->filterArguments($arguments));

		return is_int($returnValue) ? $returnValue : CommandInterface::STATUS_SUCCESS;
	}
}
