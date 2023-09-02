<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\server;

use mako\application\Application;
use mako\cli\input\arguments\Argument;
use mako\reactor\Command;

use function dirname;
use function escapeshellarg;
use function escapeshellcmd;
use function fclose;
use function fsockopen;
use function gethostbyname;
use function gethostname;
use function passthru;
use function sprintf;

/**
 * Server command.
 */
class Server extends Command
{
	/**
	 * Number of ports to try before giving up.
	 *
	 * @var int
	 */
	protected const MAX_PORTS_TO_TRY = 10;

	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Starts the local development server.';

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-a|--address', 'Address to run the server on', Argument::IS_OPTIONAL),
			new Argument('-d|--docroot', 'Path to the document root', Argument::IS_OPTIONAL),
			new Argument('-p|--port', 'Port to run the server on', Argument::IS_OPTIONAL | Argument::IS_INT),
		];
	}

	/**
	 * Tries to find an avaiable port closest to the desired port.
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

		$docroot ??= dirname($application->getPath()) . '/public';

		// Tell the user where the server will be running

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= "<underlined>http://{$host}:{$availablePort}</underlined> ";
		$message .= '<yellow>(ctrl+c to stop)</yellow> ...';

		$this->write($message);

		// Start the server

		passthru(
			escapeshellcmd(PHP_BINDIR . '/php')
			. ' -S ' . escapeshellarg("{$address}:{$availablePort}")
			. ' -t ' . escapeshellarg($docroot)
			. ' ' . escapeshellarg(__DIR__ . '/router.php')
		);
	}
}
