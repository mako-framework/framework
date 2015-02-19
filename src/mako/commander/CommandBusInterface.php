<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\commander;

use mako\onion\Onion;
use mako\syringe\Container;

/**
 * Command bus interface.
 *
 * @author  Yamada Taro
 */

interface CommandBusInterface
{
	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   null|\mako\syringe\Container  $container  Container
	 * @param   null|\mako\onion\Onion        $onion      Onion
	 */

	public function __construct(Container $container = null, Onion $onion = null);

	/**
	 * Dispatches the command to the command handler and returns the result.
	 *
	 * @access  public
	 * @param   \mako\commander\CommandInterface|string  $command     Command
	 * @param   array                                    $parameters  Parameters
	 * @param   array                                    $middleware  Middleware
	 * @return  mixed
	 */

	public function dispatch($command, array $parameters = []);
}