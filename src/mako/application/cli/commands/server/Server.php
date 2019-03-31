<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\server;

use mako\application\Application;
use mako\reactor\Command;

use function dirname;
use function fclose;
use function fsockopen;
use function gethostbyname;
use function gethostname;
use function passthru;
use function sprintf;

/**
 * Server command.
 *
 * @author Frederic G. Østby
 */
class Server extends Command
{
	const MAX_PORTS_TO_TRY = 10;

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
	 * Tries to find an avaiable port closest to the desired port.
	 *
	 * @param  int      $port Desired port number
	 * @return int|null
	 */
	protected function findAvailablePort(int $port): ?int
	{
		$attempts = 0;

		while($attempts++ < static::MAX_PORTS_TO_TRY)
		{
			if(($socket = @fsockopen('localhost', $port, $errorNumber, $errorString, 0.1)) === false)
			{
				return $port;
			}

			fclose($socket);

			$port++;
		}

		return null;
	}

	/**
	 * Executes the command.
	 *
	 * @param  \mako\application\Application $application Application instance
	 * @param  int                           $port        Port
	 * @param  string                        $address     Address
	 * @param  string|null                   $docroot     Document root
	 * @return int|void
	 */
	public function execute(Application $application, int $port = 8000, string $address = 'localhost', ?string $docroot = null)
	{
		// Attempt to find an available port

		if(($availablePort = $this->findAvailablePort($port)) === null)
		{
			$this->error(sprintf('Unable to start server. Ports %s to %s are already in use.', $port, $port + static::MAX_PORTS_TO_TRY));

			return static::STATUS_ERROR;
		}

		// Determine which hostname to show the user

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		// Set the document root

		$docroot = $docroot ?? dirname($application->getPath()) . '/public';

		// Tell the user where the server will be running

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= '<underlined>http://' . $host . ':' . $availablePort . '</underlined> ';
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->write($message);

		// Start the server

		passthru(PHP_BINDIR . '/php -S ' . $address . ':' . $availablePort . ' -t ' . $docroot . ' ' . __DIR__ . '/router.php');
	}
}
