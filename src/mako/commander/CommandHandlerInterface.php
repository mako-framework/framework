<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\commander;

use mako\commander\CommandInterface;

/**
 * Command handler interface.
 *
 * @author  Yamada Taro
 */

interface CommandHandlerInterface
{
	/**
	 * Handles a command.
	 *
	 * @access  public
	 * @param   \mako\commander\CommandInterface  $command  Command
	 * @return  mixed
	 */

	public function handle(CommandInterface $command);
}