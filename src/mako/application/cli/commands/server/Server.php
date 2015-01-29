<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\server;

use mako\application\Application;
use mako\reactor\Command;

/**
 * Server command.
 *
 * @author  Frederic G. Østby
 */

class Server extends Command
{
	/**
	 * Command information.
	 *
	 * @var array
	 */

	protected $commandInformation =
	[
		'description' => 'Starts the local development server.',
		'arguments'   => [],
		'options'     =>
		[
			'port' =>
			[
				'optional'    => true,
				'description' => 'Port to run the server on',
			],
			'address' =>
			[
				'optional'    => true,
				'description' => 'Address to run the server on',
			],
			'docroot' =>
			[
				'optional'    => true,
				'description' => 'Path to the document root',
			],
		],
	];

	/**
	 * Executes the command.
	 *
	 * @access  public
	 * @param   \mako\application\Application  $application  Application instance
	 * @param   int                            $port         Port
	 * @param   string                         $address      Address
	 * @param   null|string                    $docroot      Document root
	 */

	public function execute(Application $application, $port = 8000, $address = 'localhost', $docroot = null)
	{
		$docroot = $docroot ?: dirname($application->getPath()) . '/public';

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= '<underlined>http://' . $host . ':' . $port . '</underlined> ';
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->write($message);

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/router.php');
	}
}