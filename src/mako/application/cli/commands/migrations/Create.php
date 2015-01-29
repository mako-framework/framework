<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\Application;
use mako\file\FileSystem;
use mako\reactor\Command;

/**
 * Command that creates a migration.
 *
 * @author  Frederic G. Østby
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
		'arguments'   => [],
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
	 * @access  public
	 * @param   \mako\application\Application  $application  Application instance
	 * @param   \mako\file\FileSystem          $fileSystem   File system instance
	 * @param   string                         $package      Package name
	 * @param   string                         $description  Migration description
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

		$migration = str_replace($search, $replace, $fileSystem->getContents(__DIR__ . '/resources/migration.template'));

		try
		{
			$fileSystem->putContents($path, $migration);
		}
		catch(Exception $e)
		{
			$this->error('Failed to create migration. Make sure that the migrations directory is writable.');

			return;
		}

		$this->write(vsprintf('Migration created at [ %s ].', [$path]));
	}
}