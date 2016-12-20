<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\helpers;

use mako\database\query\Query;

/**
 * Query builder helper interface.
 *
 * @author  Frederic G. Østby
 */
interface HelperInterface
{
	/**
	 * Inserts data into the chosen table and returns the auto increment id.
	 *
	 * @access  public
	 * @param   \mako\database\query\Query  $query       Query builder instance
	 * @param   array                       $values      Associative array of column values
	 * @param   null|string                 $primaryKey  Primary key name
	 * @return  int|bool
	 */
	public function insertAndGetId(Query $query, array $values, string $primaryKey = null);
}
