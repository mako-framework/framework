<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

/**
 * Postgres database connection.
 */
class Postgres extends Connection
{
	/**
	 * {@inheritDoc}
	 */
	protected bool $supportsTransactionalDDL = true;
}
