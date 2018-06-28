<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

/**
 * Command that runs all outstanding migrations.
 *
 * @author Frederic G. Østby
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
		'options'     =>
		[
			'database' =>
			[
				'optional'    => true,
				'description' => 'Sets which database connection to use',
			],
		],
	];

	/**
	 * Executes the command.
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
			$this->runMigration($migration, 'up', $batch);
		}

		$this->write('Ran the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
	}
}
