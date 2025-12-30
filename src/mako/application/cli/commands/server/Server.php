<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\server;

use mako\application\Application;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\cli\output\components\Hyperlink;
use mako\cli\output\components\hyperlink\Theme;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;

use function array_map;
use function dirname;
use function escapeshellarg;
use function escapeshellcmd;
use function fclose;
use function fsockopen;
use function gethostbyname;
use function gethostname;
use function implode;
use function passthru;
use function sleep;
use function sprintf;

/**
 * Server command.
 */
#[CommandDescription('Starts the local development server.')]
#[CommandArguments(
	new NamedArgument('address', 'a', 'Address to run the server on', Argument::IS_OPTIONAL),
	new NamedArgument('docroot', 'd', 'Path to the document root', Argument::IS_OPTIONAL),
	new NamedArgument('port', 'p', 'Port to run the server on', Argument::IS_OPTIONAL | Argument::IS_INT),
	new NamedArgument('auto-restart', 'r', 'Automatically restart the server on fatal errors', Argument::IS_OPTIONAL | Argument::IS_BOOL),
	new NamedArgument('ini', 'i', 'PHP INI entry', Argument::IS_OPTIONAL | Argument::IS_ARRAY),
)]
class Server extends Command
{
	/**
	 * Number of ports to try before giving up.
	 */
	protected const int MAX_PORTS_TO_TRY = 10;

	/**
	 * Tries to find an avaiable port closest to the desired port.
	 */
	protected function findAvailablePort(int $port): ?int
	{
		$attempts = 0;

		while ($attempts++ < static::MAX_PORTS_TO_TRY) {
			if (($socket = @fsockopen('localhost', $port, $errorNumber, $errorString, 0.1)) === false) {
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
	public function execute(
		Application $application,
		int $port = 8000,
		string $address = 'localhost',
		?string $docroot = null,
		bool $autoRestart = false,
		array $ini = []
	) {
		// Attempt to find an available port

		if (($availablePort = $this->findAvailablePort($port)) === null) {
			$this->error(sprintf('Unable to start server. Ports <bold>%s</bold> to <bold>%s</bold> are already in use.', $port, $port + static::MAX_PORTS_TO_TRY));

			return static::STATUS_ERROR;
		}

		// Determine which hostname to show the user

		$host = ($address === '0.0.0.0') ? gethostbyname(gethostname()) : $address;

		// Set the document root

		$docroot ??= dirname($application->getPath()) . '/public';

		// Tell the user where the server will be running

		$message  = 'Starting <green>Mako</green> development server at ';
		$message .= (new Hyperlink($this->output, new Theme('<underlined>%s</underlined>')))->render("http://{$host}:{$availablePort}");
		$message .= ' <yellow>(ctrl+c to stop)</yellow> ...';

		$this->nl();
		$this->write($message);
		$this->nl();

		// Build INI entries

		$iniEntries = '';

		if ($ini !== []) {
			$iniEntries = ' -d ' . implode(' -d ', array_map(escapeshellarg(...), $ini));
		}

		// Start the server

		$starts = 0;

		do {
			$starts++;

			if ($starts > 1) {
				$this->nl();
				$this->write('Restarting server...');
				$this->nl();
			}

			passthru(
				escapeshellcmd(PHP_BINDIR . '/php')
				. $iniEntries
				. ' -S ' . escapeshellarg("{$address}:{$availablePort}")
				. ' -t ' . escapeshellarg($docroot)
				. ' ' . escapeshellarg(__DIR__ . '/router.php')
			);

			sleep(1);
		}
		while ($autoRestart);
	}
}
