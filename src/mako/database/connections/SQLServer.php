<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Override;

/**
 * SQLServer database connection.
 */
class SQLServer extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	//#[Override]
	protected const bool SUPPORTS_TRANSACTIONAL_DDL = true;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function createSavepoint(): bool
	{
		return $this->pdo->exec("SAVE TRANSACTION transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function rollBackSavepoint(): bool
	{
		return $this->pdo->exec("ROLLBACK TRANSACTION transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}
}
