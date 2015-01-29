<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\Command;

/**
 * Command that runs all outstanding migrations.
 *
 * @author  Frederic G. Østby
 */

class Up extends Command
{
	/**
	 * Command information.
	 *
	 * @var array
	 */

	protected $commandInformation =
	[
		'description' => 'Runs all outstanding migrations.',
		'arguments'   => [],
		'options'     => [],
	];

	/**
	 * Executes the command.
	 *
	 * @access  public
	 */

	public function execute()
	{
		$migrations = $this->getOutstanding();

		if(empty($migrations))
		{
			$this->write('<blue>There are no outstanding migrations.</blue>');

			return;
		}

		$batch = $this->builder()->max('batch') + 1;

		foreach($migrations as $migration)
		{
			$this->runMigration($migration, 'up');

			$this->builder()->insert(['batch' => $batch, 'package' => $migration->package, 'version' => $migration->version]);
		}

		$this->write('Ran the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
	}
}