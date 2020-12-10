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
	 * @param  \mako\database\query\Query $query      Query builder instance
	 * @param  array                      $values     Associative array of column values
	 * @param  string|null                $primaryKey Primary key name
	 * @return int|false
	 */
	public function insertAndGetId(Query $query, array $values, ?string $primaryKey = null);
}
