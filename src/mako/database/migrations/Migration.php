<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\migrations;

use mako\database\ConnectionManager;
use mako\syringe\ContainerAwareTrait;

/**
 * Base migration.
 *
 * @author  Frederic G. Østby
 */

abstract class Migration
{
	use ContainerAwareTrait;

	/**
	 * Connection manager instance.
	 *
	 * @var \mako\database\ConnectionManager
	 */

	protected $database;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\database\ConnectionManager  $connectionManager  Connection manager instance
	 */

	public function __construct(ConnectionManager $connectionManager)
	{
		$this->database = $connectionManager;
	}

	/**
	 * Returns the migration description.
	 *
	 * @access  public
	 * @return  string|null
	 */

	public function getDescription()
	{
		return isset($this->description) && !empty($this->description) ? $this->description : null;
	}

	/**
	 * Makes changes to the database structure.
	 *
	 * @access  public
	 */

	abstract public function up();

	/**
	 * Reverts the database changes.
	 *
	 * @access  public
	 */

	abstract public function down();
}