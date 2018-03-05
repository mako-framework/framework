<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\Application;
use mako\file\FileSystem;
use mako\reactor\Command;
use Throwable;

/**
 * Command that creates a migration.
 *
 * @author Frederic G. Østby
 */
class Create extends Command
{
	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Creates a new migration.',
		'options'     =>
		[
			'package' =>
			[
				'optional'    => true,
				'description' => 'Package name',
			],
			'description' =>
			[
				'optional'    => true,
				'description' => 'Migration description',
			],
		],
	];

	/**
	 * Executes the command.
	 *
	 * @param \mako\application\Application $application Application instance
	 * @param \mako\file\FileSystem         $fileSystem  File system instance
	 * @param string                        $package     Package name
	 * @param string                        $description Migration description
	 */
	public function execute(Application $application, FileSystem $fileSystem, $package = null, $description = null)
	{
		// Get file path and namespace

		if(empty($package))
		{
			$namespace = $application->getNamespace() . '\\migrations';

			$path = $application->getPath() . '/migrations/';
		}
		else
		{
			$package = $application->getPackage($package);

			$namespace = $package->getClassNamespace() . '\\migrations';

			$path = $package->getPath() . '/src/migrations/';
		}

		$path .= 'Migration_' . ($version = gmdate('YmdHis')) . '.php';

		// Create migration

		$description = str_replace("'", "\'", $description);

		$search = ['{{namespace}}', '{{version}}', '{{description}}'];

		$replace = [$namespace, $version, $description];

		$migration = str_replace($search, $replace, $fileSystem->get(__DIR__ . '/resources/migration.template'));

		try
		{
			$fileSystem->put($path, $migration);
		}
		catch(Throwable $e)
		{
			$this->error('Failed to create migration. Make sure that the migrations directory is writable.');

			return Command::STATUS_ERROR;
		}

		$this->write(vsprintf('Migration created at [ %s ].', [$path]));
	}
}
