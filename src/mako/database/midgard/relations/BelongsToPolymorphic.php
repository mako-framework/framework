<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;

/**
 * Belongs to polymorphic relation.
 */
class BelongsToPolymorphic extends BelongsTo
{
	/**
	 * Constructor.
	 */
	public function __construct(Connection $connection, ORM $origin, ORM $model, string $polymorphicType)
	{
		parent::__construct($connection, $origin, $model, "{$polymorphicType}_id");
	}
}
