<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\traits\RollbackTrait;

/**
 * Command that rolls back the last batch of migrations.
 *
 * @author Frederic G. Østby
 */
class Down extends Command
{
	use RollbackTrait;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Rolls back the last batch of migrations.',
		'options'     =>
		[
			'batches' =>
			[
				'optional'    => true,
				'description' => 'Number of batches to roll back',
			],
			'database' =>
			[
				'optional'    => true,
				'description' => 'Sets which database connection to use',
			],
		],
	];

	/**
	 * Executes the command.
	 *
	 * @param int $batches Number of batches to roll back
	 */
	public function execute(int $batches = 1): void
	{
		$this->rollback($batches);
	}
}
