<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations\traits;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;

/**
 * Has one or has many polymorphic relation.
 *
 * @author Frederic G. Østby
 */
trait HasOneOrManyPolymorphicTrait
{
	/**
	 * Polymorphic type.
	 *
	 * @var string
	 */
	protected $polymorphicType;

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
		$this->polymorphicType = "{$polymorphicType}_type";

		parent::__construct($connection, $parent, $related, "{$polymorphicType}_id");

		$this->where("{$this->table}.{$this->polymorphicType}", '=', $parent->getClass());
	}

	/**
	 * Creates a related record.
	 *
	 * @param  \mako\database\midgard\ORM|array $related Related record
	 * @return \mako\database\midgard\ORM
	 */
	public function create($related)
	{
		if($related instanceof $this->model)
		{
			$related->{$this->polymorphicType} = $this->parent->getClass();
		}
		else
		{
			$related[$this->polymorphicType] = $this->parent->getClass();
		}

		return parent::create($related);
	}
}
