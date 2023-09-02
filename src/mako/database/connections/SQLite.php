<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

/**
 * SQLite database connection.
 */
class SQLite extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	protected bool $supportsTransactionalDDL = true;
}
