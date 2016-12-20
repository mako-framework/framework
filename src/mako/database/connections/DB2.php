<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use mako\database\connections\Connection;

/**
 * DB2 database connection.
 *
 * @author Frederic G. Østby
 */
class DB2 extends Connection
{
	/**
	 * {@inheritdoc}
	 */
	public function isAlive(): bool
	{
		try
		{
			$this->pdo->query('SELECT 1 FROM SYSIBM.SYSDUMMY1');
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
	}
}
