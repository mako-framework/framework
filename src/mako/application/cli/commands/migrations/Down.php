<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\Command;
use mako\application\cli\commands\migrations\RollbackTrait;

/**
 * Command that rolls back the last batch of migrations.
 *
 * @author  Frederic G. Østby
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
		'arguments'   => [],
		'options'     =>
		[
			'batches' =>
			[
				'optional'    => true,
				'description' => 'Number of batches to roll back'
			],
		],
	];

	/**
	 * Executes the command.
	 *
	 * @access  public
	 * @param   string  $batches  Number of batches to roll back
	 */

	public function execute($batches = 1)
	{
		$this->rollback($batches);
	}
}