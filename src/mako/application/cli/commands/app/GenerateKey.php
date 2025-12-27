<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;
use mako\security\Key;

/**
 * Command that generates an encryption key.
 */
#[CommandDescription('Generates a 256-bit encryption key.')]
class GenerateKey extends Command
{
	/**
	 * Executes the command.
	 */
	public function execute(): void
	{
		$this->nl();
		$this->write('Your encryption key: "<yellow>' . Key::generateEncoded() . '</yellow>".');
		$this->nl();
	}
}
