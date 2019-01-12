<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

/**
 * SQLite database connection.
 *
 * @author Frederic G. Østby
 */
class SQLite extends Connection
{
	/**
	 * {@inheritdoc}
	 */
	protected $supportsTransactionalDDL = true;
}
