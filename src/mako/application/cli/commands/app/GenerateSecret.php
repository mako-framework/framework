<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\app;

use mako\application\Application;
use mako\file\FileSystem;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;
use mako\security\Key;

use function preg_replace;

/**
 * Command that generates a new application secret.
 */
#[CommandDescription('Generates a new application secret.')]
class GenerateSecret extends Command
{
	/**
	 * Executes the command.
	 *
	 * @return int|void
	 */
	public function execute(Application $application, FileSystem $fileSystem)
	{
		$configFile = "{$application->getPath()}/config/application.php";

		if (!$fileSystem->isWritable($configFile)) {
			$this->nl();
			$this->error('Unable to generate a new secret. Make sure that the "<bold>app/config/application.php</bold>" file is writable.');
			$this->nl();

			return static::STATUS_ERROR;
		}

		$secret = Key::generateEncoded();

		$contents = $fileSystem->get($configFile);

		$contents = preg_replace("/'secret'(\s*)=>(\s*)'(.*)',/", "'secret'$1=>$2'{$secret}',", $contents);

		$fileSystem->put($configFile, $contents);

		$this->nl();
		$this->write('A new application secret has been generated.');
		$this->nl();
	}
}
