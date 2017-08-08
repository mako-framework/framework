<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\commander;

/**
 * Self handling command interface.
 *
 * @author Yamada Taro
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
