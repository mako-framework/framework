<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\helpers;

use mako\database\query\Query;

/**
 * Query builder postgres helper.
 */
class Postgres implements HelperInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function insertAndGetId(Query $query, array $values, ?string $primaryKey = null)
	{
		if($query->insert($values) === false)
		{
			return false;
		}

		$sequence = "{$query->getTable()}_{$primaryKey}_seq";

		return $query->getConnection()->getPDO()->lastInsertId($sequence);
	}
}
