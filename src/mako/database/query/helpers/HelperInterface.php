<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\helpers;

use mako\database\query\Query;

/**
 * Query builder helper interface.
 */
interface HelperInterface
{
	/**
	 * Inserts data into the chosen table and returns the auto increment id.
	 *
	 * @return false|int
	 */
	public function insertAndGetId(Query $query, array $values = [], ?string $primaryKey = null);
}
