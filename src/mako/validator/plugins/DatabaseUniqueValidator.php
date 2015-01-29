<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use mako\database\ConnectionManager;
use mako\validator\plugins\ValidatorPlugin;

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
	 * Checks that the value doesn't exist in the database table.
	 *
	 * @access  public
	 * @param   string   $input   Input
	 * @param   string   $table   Table name
	 * @param   string   $column  Column name
	 * @param   string   $value   Allowed value
	 * @return  boolean
	 */

	public function validate($input, $table, $column, $value = null)
	{
		$query = $this->connectionManager->builder()->table($table)->where($column, '=', $input);

		if(!empty($value))
		{
			$query->where($column, '!=', $value);
		}

		return ($query->count() == 0);
	}
}