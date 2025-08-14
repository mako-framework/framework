<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Override;
use PDOException;

/**
 * Firebird database connection.
 */
class Firebird extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isAlive(): bool
	{
		try {
			$this->pdo->query('SELECT 1 FROM RDB$DATABASE');
		}
		catch (PDOException $e) {
			return false;
		}

		return true;
	}
}
