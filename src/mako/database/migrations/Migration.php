<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\migrations;

use mako\database\ConnectionManager;
use mako\database\connections\Connection;
use mako\syringe\traits\ContainerAwareTrait;

/**
 * Base migration.
 */
abstract class Migration
{
	use ContainerAwareTrait;

	/**
	 * Should a transaction be used if possible?
	 *
	 * @var bool
	 */
	protected $useTransaction = true;

	/**
	 * Connection name.
	 *
	 * @var string|null
	 */
	protected $connectionName;

	/**
	 * Migration description.
	 *
	 * @var string|null
	 */
	protected $description;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\ConnectionManager $database Connection manager instance
	 */
	public function __construct(
		protected ConnectionManager $database
	)
	{}

	/**
	 * Returns the connection name.
	 *
	 * @return string|null
	 */
	public function getConnectionName(): ?string
	{
		return $this->connectionName;
	}

	/**
	 * Returns the chosen connection.
	 *
	 * @return \mako\database\connections\Connection
	 */
	public function getConnection(): Connection
	{
		return $this->database->getConnection($this->connectionName);
	}

	/**
	 * Should we execute this migration in a transaction?
	 *
	 * @return bool
	 */
	public function useTransaction(): bool
	{
		return $this->useTransaction && $this->getConnection()->supportsTransactionalDDL();
	}

	/**
	 * Returns the migration description.
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return !empty($this->description) ? $this->description : '';
	}

	/**
	 * Makes changes to the database structure.
	 */
	abstract public function up(): void;

	/**
	 * Reverts the database changes.
	 */
	abstract public function down(): void;
}
