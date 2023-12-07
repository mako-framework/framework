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
	 */
	protected bool $useTransaction = true;

	/**
	 * Connection name.
	 */
	protected null|string $connectionName = null;

	/**
	 * Migration description.
	 */
	protected string $description = '';

	/**
	 * Constructor.
	 */
	public function __construct(
		protected ConnectionManager $database
	) {
	}

	/**
	 * Returns the connection name.
	 */
	public function getConnectionName(): ?string
	{
		return $this->connectionName;
	}

	/**
	 * Returns the chosen connection.
	 */
	public function getConnection(): Connection
	{
		return $this->database->getConnection($this->connectionName);
	}

	/**
	 * Should we execute this migration in a transaction?
	 */
	public function useTransaction(): bool
	{
		return $this->useTransaction && $this->getConnection()->supportsTransactionalDDL();
	}

	/**
	 * Returns the migration description.
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
