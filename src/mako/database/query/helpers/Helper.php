<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\helpers;

use mako\database\query\Query;
use mako\database\query\helpers\HelperInterface;

/**
 * Query builder helper.
 *
 * @author Frederic G. Østby
 */
class Helper implements HelperInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function insertAndGetId(Query $query, array $values, string $primaryKey = null)
	{
		if($query->insert($values) === false)
		{
			return false;
		}

		return $query->getConnection()->getPDO()->lastInsertId();
	}
}
