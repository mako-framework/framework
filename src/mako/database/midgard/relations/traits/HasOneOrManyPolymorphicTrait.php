<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations\traits;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;
use Override;

/**
 * Has one or has many polymorphic relation.
 */
trait HasOneOrManyPolymorphicTrait
{
	/**
	 * Polymorphic type.
	 */
	protected string $polymorphicType;

	/**
	 * Constructor.
	 */
	public function __construct(Connection $connection, ORM $origin, ORM $model, string $polymorphicType)
	{
		$this->polymorphicType = "{$polymorphicType}_type";

		parent::__construct($connection, $origin, $model, "{$polymorphicType}_id");

		$this->where("{$this->table}.{$this->polymorphicType}", '=', $origin->getClass());
	}

	/**
	 * Creates a related record.
	 */
	#[Override]
	public function create(array|ORM $related): ORM
	{
		if ($related instanceof $this->model) {
			$related->{$this->polymorphicType} = $this->origin->getClass();
		}
		else {
			$related[$this->polymorphicType] = $this->origin->getClass();
		}

		return parent::create($related);
	}
}
