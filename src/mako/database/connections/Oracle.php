<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Override;
use PDOException;

/**
 * Oracle database connection.
 */
class Oracle extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function isAlive(): bool
	{
		try {
			$this->pdo->query('SELECT 1 FROM "DUAL"');
		}
		catch (PDOException $e) {
			return false;
		}

		return true;
	}
}
