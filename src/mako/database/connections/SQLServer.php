<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use mako\database\connections\Connection;

/**
 * SQLServer database connection.
 *
 * @author Frederic G. Østby
 */
class SQLServer extends Connection
{
	/**
	 * {@inheritdoc}
	 */
	protected function createSavepoint(): bool
	{
		return $this->pdo->exec('SAVE TRANSACTION transactionNestingLevel' . $this->transactionNestingLevel) !== false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function rollBackSavepoint(): bool
	{
		return $this->pdo->exec('ROLLBACK TRANSACTION transactionNestingLevel' . $this->transactionNestingLevel) !== false;
	}
}
