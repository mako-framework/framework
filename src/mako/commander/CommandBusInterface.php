<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\commander;

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
	 * @param   \mako\syringe\Container|null  $container  Container
	 */

	public function __construct(Container $container = null);

	/**
	 * Handles a command.
	 *
	 * @access  public
	 * @param   \mako\commander\CommandInterface|string  $command     Command
	 * @param   array                                    $parameters  Parameters
	 * @return  mixed
	 */

	public function handle($command, array $parameters = []);
}