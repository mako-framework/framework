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
 *
 * @author Frederic G. Østby
 */
class BelongsToPolymorphic extends BelongsTo
{
	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection      Database connection
	 * @param \mako\database\midgard\ORM            $parent          Parent model
	 * @param \mako\database\midgard\ORM            $related         Related model
	 * @param string                                $polymorphicType Polymorphic type
	 */
	public function __construct(Connection $connection, ORM $parent, ORM $related, string $polymorphicType)
	{
		parent::__construct($connection, $parent, $related, "{$polymorphicType}_id");
	}
}
