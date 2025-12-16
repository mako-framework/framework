<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Override;

/**
 * SQLite database connection.
 */
class SQLite extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected bool $supportsTransactionalDDL = true;
}
