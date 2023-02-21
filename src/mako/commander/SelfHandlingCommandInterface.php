<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\commander;

/**
 * Self handling command interface.
 *
 * @deprecated
 */
interface SelfHandlingCommandInterface
{
	/**
	 * Handles the command.
	 *
	 * @return mixed
	 */
	public function handle();
}
