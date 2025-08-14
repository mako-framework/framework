<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Override;
use PDOStatement;

use function is_bool;

/**
 * MySQL database connection.
 */
class MySQL extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function bindParameter(PDOStatement $statement, int $key, $value): void
	{
		if (is_bool($value)) {
			$value = $value ? 1 : 0;
		}

		parent::bindParameter($statement, $key, $value);
	}
}
