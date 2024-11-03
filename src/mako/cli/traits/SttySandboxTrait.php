<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\traits;

use function exec;
use function shell_exec;

/**
 * Stty sandbox trait.
 */
trait SttySandboxTrait
{
	/**
	 * Executes a callable in a stty sandbox.
	 */
	public function sttySandbox(callable $callable): mixed
	{
		$settings = shell_exec('stty -g');

		try {
			return $callable();
		}
		finally {
			exec("stty {$settings}");
		}
	}
}
