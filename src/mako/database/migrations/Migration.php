<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\migrations;

use mako\database\ConnectionManager;
use mako\syringe\traits\ContainerAwareTrait;

/**
 * Base migration.
 *
 * @author Frederic G. Østby
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
	 * @param \mako\database\ConnectionManager $connectionManager Connection manager instance
	 */
	public function __construct(ConnectionManager $connectionManager)
	{
		$this->database = $connectionManager;
	}

	/**
	 * Returns the migration description.
	 *
	 * @return string|null
	 */
	public function getDescription()
	{
		return isset($this->description) && !empty($this->description) ? $this->description : null;
	}

	/**
	 * Makes changes to the database structure.
	 */
	abstract public function up();

	/**
	 * Reverts the database changes.
	 */
	abstract public function down();
}
