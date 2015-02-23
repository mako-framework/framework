<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor;

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

	protected function resolve($command)
	{
		return $this->container->get($command);
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

	public function dispatch($command, array $arguments)
	{
		$command = $this->resolve($command);

		if($command->shouldExecute())
		{
			$this->execute($command, $arguments);
		}
	}
}