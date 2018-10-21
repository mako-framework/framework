<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\server;

use mako\application\Application;
use mako\reactor\Command;

use function dirname;
use function gethostbyname;
use function gethostname;
use function passthru;

/**
 * Server command.
 *
 * @author Frederic G. Østby
 */
class Server extends Command
{
	/**
	 * Make the command strict.
	 *
	 * @var bool
	 */
	protected $isStrict = true;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Starts the local development server.',
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
	 * @param \mako\application\Application $application Application instance
	 * @param int                           $port        Port
	 * @param string                        $address     Address
	 * @param string|null                   $docroot     Document root
	 */
	public function execute(Application $application, $port = 8000, $address = 'localhost', $docroot = null)
	{
		$docroot = $docroot ?? dirname($application->getPath()) . '/public';

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= '<underlined>http://' . $host . ':' . $port . '</underlined> ';
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->write($message);

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $port . ' -t ' . $docroot . ' ' . __DIR__ . '/router.php');
	}
}
