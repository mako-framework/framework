<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\Application;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\file\FileSystem;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;
use mako\reactor\Command;
use Throwable;

use function gmdate;
use function sprintf;
use function str_replace;

/**
 * Command that creates a migration.
 */
#[CommandDescription('Creates a new migration.')]
#[CommandArguments(
	new NamedArgument('description', 'd', 'Migration description', Argument::IS_OPTIONAL),
	new NamedArgument('package', 'p', 'Package name', Argument::IS_OPTIONAL),
)]
class Create extends Command
{
	/**
	 * Executes the command.
	 *
	 * @return int|void
	 */
	public function execute(Application $application, FileSystem $fileSystem, $package = null, $description = null)
	{
		// Get file path and namespace

		if (empty($package)) {
			$namespace = "{$application->getNamespace()}\\migrations";

			$path = "{$application->getPath()}/migrations/";
		}
		else {
			$package = $application->getPackage($package);

			$namespace = "{$package->getClassNamespace()}\\migrations";

			$path = "{$package->getPath()}/src/migrations/";
		}

		$path .= 'Migration_' . ($version = gmdate('YmdHis')) . '.php';

		// Create migration

		$description = str_replace("'", "\'", $description ?? '');

		$search = ['{{namespace}}', '{{version}}', '{{description}}'];

		$replace = [$namespace, $version, $description];

		$migration = str_replace($search, $replace, $fileSystem->get(__DIR__ . '/resources/migration.template'));

		try {
			$fileSystem->put($path, $migration);
		}
		catch (Throwable $e) {
			$this->error('Failed to create migration. Make sure that the migrations directory is writable.');

			return static::STATUS_ERROR;
		}

		$this->write(sprintf('Migration created at "<yellow>%s</yellow>".', $path));
	}
}
