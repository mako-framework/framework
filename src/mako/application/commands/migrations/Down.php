<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\commands\migrations;

use mako\application\commands\migrations\Command;
use mako\application\commands\migrations\RollbackTrait;

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
		'arguments'   => 
		[
			'batches' => 
			[
				'optional'    => true,
				'description' => 'Number of batches to roll back'
			],
		],
		'options'     => [],
	];

	/**
	 * Executes the command.
	 * 
	 * @access  public
	 * @param   string  $arg2  Number of batches to roll back
	 */

	public function execute($arg2 = 1)
	{
		$this->rollback($arg2);
	}
}