<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\commander;

use mako\onion\Onion;
use mako\syringe\Container;

use function get_class;
use function strlen;
use function strrpos;
use function substr;
use function substr_replace;

/**
 * Command bus.
 */
class CommandBus implements CommandBusInterface
{
	/**
	 * Command suffix.
	 *
	 * @var string
	 */
	const COMMAND_SUFFIX = 'Command';

	/**
	 * Handler suffix.
	 *
	 * @var string
	 */
	const HANDLER_SUFFIX = 'Handler';

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Onion.
	 *
	 * @var \mako\onion\Onion
	 */
	protected $onion;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(?Container $container = null, ?Onion $onion = null)
	{
		$this->container = $container ?? new Container;

		$this->onion = $onion ?? new Onion($this->container);
	}

	/**
	 * Adds middleware.
	 *
	 * @param  string $middleware Middleware class
	 * @param  bool   $inner      Add an inner layer?
	 * @return int
	 */
	public function addMiddleware(string $middleware, bool $inner = true): int
	{
		return $inner ? $this->onion->addInnerLayer($middleware) : $this->onion->addOuterLayer($middleware);
	}

	/**
	 * Resolves the command.
	 *
	 * @param  \mako\commander\CommandInterface|string $command    Command
	 * @param  array                                   $parameters Parameters
	 * @return \mako\commander\CommandInterface
	 */
	protected function resolveCommand($command, array $parameters): CommandInterface
	{
		if($command instanceof CommandInterface)
		{
			return $command;
		}

		return $this->container->get($command, $parameters);
	}

	/**
	 * Resolves the command handler.
	 *
	 * @param  \mako\commander\CommandInterface        $command Command
	 * @return \mako\commander\CommandHandlerInterface
	 */
	protected function resolveCommandHandler(CommandInterface $command): CommandHandlerInterface
	{
		$class = get_class($command);

		// Build handler class name

		$commandSuffixLength = strlen(static::COMMAND_SUFFIX);

		if(static::COMMAND_SUFFIX === substr($class, -$commandSuffixLength))
		{
			$handler = substr_replace($class, static::HANDLER_SUFFIX, strrpos($class, static::COMMAND_SUFFIX), $commandSuffixLength);
		}
		else
		{
			$handler = $class . static::HANDLER_SUFFIX;
		}

		// Return handler instance

		return $this->container->get($handler);
	}

	/**
	 * Executes the command handler.
	 *
	 * @param  \mako\commander\CommandHandlerInterface $handler Command handler
	 * @param  \mako\commander\CommandInterface        $command Command
	 * @return mixed
	 */
	protected function executeCommandHandler(CommandHandlerInterface $handler, CommandInterface $command)
	{
		return $handler->handle($command);
	}

	/**
	 * Handles the command.
	 *
	 * @param  \mako\commander\CommandInterface $command Command
	 * @return mixed
	 */
	protected function handle(CommandInterface $command)
	{
		if($command instanceof SelfHandlingCommandInterface)
		{
			return $command->handle();
		}

		$handler = $this->resolveCommandHandler($command);

		return $this->executeCommandHandler($handler, $command);
	}

	/**
	 * Resolves the onion instance.
	 *
	 * @param  array             $middleware Middleware
	 * @return \mako\onion\Onion
	 */
	protected function resolveOnion(array $middleware): Onion
	{
		if(empty($middleware))
		{
			return $this->onion;
		}

		$onion = clone $this->onion;

		foreach($middleware as $layer)
		{
			$onion->addLayer($layer);
		}

		return $onion;
	}

	/**
	 * {@inheritDoc}
	 */
	public function dispatch($command, array $parameters = [], array $middleware = [])
	{
		$command = $this->resolveCommand($command, $parameters);

		return $this->resolveOnion($middleware)->peel(function($command)
		{
			return $this->handle($command);
		}, [$command]);
	}
}
