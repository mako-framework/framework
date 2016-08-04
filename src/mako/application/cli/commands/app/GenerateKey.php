<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\application\Application;
use mako\security\crypto\Key;
use mako\reactor\Command;

/**
 * Command that generates an encryption key.
 *
 * @author  Frederic G. Østby
 */
class GenerateKey extends Command
{
	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Generates a 256 bit encryption key.',
		'arguments'   => [],
		'options'     => [],
	];

	/**
	 * Executes the command.
	 *
	 * @access  public
	 * @param   \mako\application\Application  $application  Application instance
	 */
	public function execute(Application $application)
	{
		$this->write('Your encryption key: "<yellow>'. Key::generateEncoded() . '</yellow>".');
	}
}