<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use \mako\database\ConnectionManager;

/**
 * Database exists plugin.
 *
 * @author  Frederic G. Østby
 */

class DatabaseExistsValidator extends \mako\validator\plugins\ValidatorPlugin implements \mako\validator\plugins\ValidatorPluginInterface
{
	/**
	 * Rule name.
	 *
	 * @var string
	 */

	protected $ruleName = 'exists';

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
	 * Validator.
	 *
	 * @access  public
	 * @param   string   $input       Input field value
	 * @param   array    $parameters  Parameters
	 * @param   array    $data        Input data array
	 * @return  boolean
	 */

	public function validate($input, $parameters, $data)
	{
		return ($this->connectionManager->builder()->table($parameters[0])->where($parameters[1], '=', $input)->count() != 0);
	}
}