<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use \mako\database\ConnectionManager;
use \mako\validator\plugins\ValidatorPlugin;

/**
 * Database unique plugin.
 *
 * @author  Frederic G. Østby
 */

class DatabaseUniqueValidator extends ValidatorPlugin
{
	/**
	 * Rule name.
	 * 
	 * @var string
	 */

	protected $ruleName = 'unique';

	/**
	 * Connection manager instance.
	 * 
	 * @var \mako\database\ConnectionManager
	 */

	protected $connectionManager;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\ConnectionManager  $connectionManager  Connection manager instance
	 */

	public function __construct(ConnectionManager $connectionManager)
	{
		$this->connectionManager = $connectionManager;
	}

	/**
	 * {@inheritdoc}
	 */

	public function validate($input, $parameters)
	{
		$query = $this->connectionManager->builder()->table($parameters[0])->where($parameters[1], '=', $input);
		
		if(isset($parameters[2]))
		{
			$query->where($parameters[1], '!=', $parameters[2]);
		}

		return ($query->count() == 0);
	}
}