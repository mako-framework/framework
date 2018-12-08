<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use function count;
use function vsprintf;

/**
 * Command that checks if there are any outstanding migrations.
 *
 * @author Frederic G. Østby
 */
class Status extends Command
{
	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Checks if there are any outstanding migrations.',
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
	public function execute(): void
	{
		$migrations = $this->getOutstanding();

		if(($count = count($migrations)) > 0)
		{
			$message = $count === 1 ? 'There is %s outstanding migration:' : 'There are %s outstanding migrations:';

			$this->write(vsprintf($message, ['<yellow>' . $count . '</yellow>']) . PHP_EOL);

			$this->outputMigrationList($migrations);
		}
		else
		{
			$this->write('<green>There are no outstanding migrations.</green>');
		}
	}
}
