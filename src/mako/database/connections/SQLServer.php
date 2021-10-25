<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

/**
 * SQLServer database connection.
 *
 * @author Frederic G. Østby
 */
class SQLServer extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	protected function createSavepoint(): bool
	{
		return $this->pdo->exec("SAVE TRANSACTION transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function rollBackSavepoint(): bool
	{
		return $this->pdo->exec("ROLLBACK TRANSACTION transactionNestingLevel{$this->transactionNestingLevel}") !== false;
	}
}
