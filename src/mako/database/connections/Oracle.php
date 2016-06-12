<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\connections;

use mako\database\connections\Connection;

/**
 * Oracle database connection.
 *
 * @author  Frederic G. Østby
 */
class Oracle extends Connection
{
	/**
	 * {@inheritdoc}
	 */
	public function isAlive()
	{
		try
		{
			$this->pdo->query('SELECT 1 FROM DUAL');
		}
		catch(PDOException $e)
		{
			return false;
		}

		return true;
	}
}