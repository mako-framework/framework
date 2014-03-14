<?php

namespace mako\validator\plugins;

use \mako\database\ConnectionManager;

/**
 * Database unique plugin.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class DatabaseUniqueValidator extends \mako\validator\plugins\ValidatorPlugin implements \mako\validator\plugins\ValidatorPluginInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Validator.
	 * 
	 * @access  public
	 * @param   string   $input       Input
	 * @param   array    $parameters  Parameters
	 * @return  boolean
	 */

	public function validate($input, $parameters)
	{
		$query = $this->connectionManager->table($parameters[0])->where($parameters[1], '=', $input);
		
		if(isset($parameters[2]))
		{
			$query->where($parameters[1], '!=', $parameters[2]);
		}

		return ($query->count() == 0);
	}
}

/** -------------------- End of file -------------------- **/