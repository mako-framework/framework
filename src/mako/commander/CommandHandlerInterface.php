<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\commander;

/**
 * Command handler interface.
 *
 * @deprecated
 */
interface CommandHandlerInterface
{
	/**
	 * Handles a command.
	 *
	 * @param  \mako\commander\CommandInterface $command Command
	 * @return mixed
	 */
	public function handle(CommandInterface $command);
}
