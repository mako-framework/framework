<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\connections;

/**
 * Postgres database connection.
 *
 * @author Frederic G. Østby
 */
class Postgres extends Connection
{
	/**
	 * {@inheritdoc}
	 */
	protected $supportsTransactionalDDL = true;
}
