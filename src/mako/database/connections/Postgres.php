<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

use Override;

/**
 * Postgres database connection.
 */
class Postgres extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected bool $supportsTransactionalDDL = true;
}
