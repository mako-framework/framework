<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\commander;

use mako\commander\CommandBusInterface;
use mako\commander\CommandHandlerInterface;
use mako\commander\CommandInterface;
use mako\commander\SelfHandlingCommandInterface;
use mako\syringe\Container;

/**
 * Command bus.
 *
 * @author  Yamada Taro
 */

class CommandBus implements CommandBusInterface
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */

	protected $container;

	/**
	 * {@inheritdoc}
	 */

	public function __construct(Container $container = null)
	{
		$this->container = $container ?: new Container;
	}

	/**
	 * Resolves the command.
	 *
	 * @access  protected
	 * @param   \mako\commander\CommandInterface|string  $command     Command
	 * @param   array                                    $parameters  Parameters
	 * @return  \mako\commander\CommandInterface
	 */

	protected function resolveCommand($command, array $parameters)
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
	 * @access  protected
	 * @param   \mako\commander\CommandInterface         $command  Command
	 * @return  \mako\commander\CommandHandlerInterface
	 */

	protected function resolveCommandHandler(CommandInterface $command)
	{
		$handler = get_class($command) . 'Handler';

		return $this->container->get($handler);
	}

	/**
	 * Executes the command handler.
	 *
	 * @access  protected
	 * @param   \mako\commander\CommandHandlerInterface  $handler  Command handler
	 * @param   \mako\commander\CommandInterface         $command  Command
	 * @return  mixed
	 */

	protected function executeCommandHandler(CommandHandlerInterface $handler, CommandInterface $command)
	{
		return $handler->handle($command);
	}

	/**
	 * Handles the command.
	 *
	 * @access  protected
	 * @param   \mako\commander\CommandInterface  $command  Command
	 * @return  mixed
	 */

	protected function handleCommand(CommandInterface $command)
	{
		if($command instanceof SelfHandlingCommandInterface)
		{
			return $command->handle();
		}

		$handler = $this->resolveCommandHandler($command);

		return $this->executeCommandHandler($handler, $command);
	}

	/**
	 * {@inheritdoc}
	 */

	public function handle($command, array $parameters = [])
	{
		$command = $this->resolveCommand($command, $parameters);

		return $this->handleCommand($command);
	}
}