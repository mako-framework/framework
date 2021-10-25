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
	 * @param \mako\database\midgard\ORM            $origin          Originating model
	 * @param \mako\database\midgard\ORM            $model           Related model
	 * @param string                                $polymorphicType Polymorphic type
	 */
	public function __construct(Connection $connection, ORM $origin, ORM $model, string $polymorphicType)
	{
		$this->polymorphicType = "{$polymorphicType}_type";

		parent::__construct($connection, $origin, $model, "{$polymorphicType}_id");

		$this->where("{$this->table}.{$this->polymorphicType}", '=', $origin->getClass());
	}

	/**
	 * Creates a related record.
	 *
	 * @param  array|\mako\database\midgard\ORM $related Related record
	 * @return \mako\database\midgard\ORM
	 */
	public function create($related)
	{
		if($related instanceof $this->model)
		{
			$related->{$this->polymorphicType} = $this->origin->getClass();
		}
		else
		{
			$related[$this->polymorphicType] = $this->origin->getClass();
		}

		return parent::create($related);
	}
}
