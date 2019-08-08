<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\reactor\Command;
use mako\security\Key;

/**
 * Command that generates an encryption key.
 *
 * @author Frederic G. Østby
 */
class GenerateKey extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Generates a 256-bit encryption key.';

	/**
	 * Executes the command.
	 */
	public function execute(): void
	{
		$this->write('Your encryption key: "<yellow>' . Key::generateEncoded() . '</yellow>".');
	}
}
