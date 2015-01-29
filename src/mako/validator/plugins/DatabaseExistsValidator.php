<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use mako\database\ConnectionManager;
use mako\validator\plugins\ValidatorPlugin;

/**
 * Database exists plugin.
 *
 * @author  Frederic G. Østby
 */

class DatabaseExistsValidator extends ValidatorPlugin
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
	 * Checks that the value exists in the database table.
	 *
	 * @access  public
	 * @param   string   $input   Input
	 * @param   string   $table   Table name
	 * @param   string   $column  Column name
	 * @return  boolean
	 */

	public function validate($input, $table, $column)
	{
		return ($this->connectionManager->builder()->table($table)->where($column, '=', $input)->count() != 0);
	}
}