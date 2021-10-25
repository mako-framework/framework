<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use PDOException;

/**
 * Firebird database connection.
 *
 * @author Frederic G. Østby
 */
class Firebird extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	public function isAlive(): bool
	{
		try
		{
			$this->pdo->query('SELECT 1 FROM RDB$DATABASE');
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
	}
}
