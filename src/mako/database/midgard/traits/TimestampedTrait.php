<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use DateTime;

/**
 * Timestamped trait.
 *
 * @author  Frederic G. Østby
 */

trait TimestampedTrait
{
	/**
	 * Returns trait hooks.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function getTimestampedTraitHooks()
	{
		return
		[
			'beforeInsert' =>
			[
				function($values, $query)
				{
					$dateTime = new DateTime;

					$createdAtColumn = $this->getCreatedAtColumn();

					$updatedAtColumn = $this->getUpdatedAtColumn();

					$this->columns[$createdAtColumn] = $dateTime;

					$this->columns[$updatedAtColumn] = $dateTime;

					return [$createdAtColumn => $dateTime, $updatedAtColumn => $dateTime] + $values;
				},
			],
			'afterInsert' =>
			[
				function($inserted)
				{
					if($inserted && $this->exists)
					{
						$this->touchRelated();
					}
				}
			],
			'beforeUpdate' =>
			[
				function($values, $query)
				{
					$dateTime = new DateTime;

					$updatedAtColumn = $this->getUpdatedAtColumn();

					$this->columns[$updatedAtColumn] = $dateTime;

					return [$updatedAtColumn => $dateTime] + $values;
				},
			],
			'afterUpdate' =>
			[
				function($updated)
				{
					if($updated > 0 && $this->exists)
					{
						$this->touchRelated();
					}
				}
			],
			'afterDelete' =>
			[
				function($deleted)
				{
					if($deleted > 0 && $this->exists)
					{
						$this->touchRelated();
					}
				}
			],
		];
	}

	/**
	 * Returns the column that holds the "created at" timestamp.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getCreatedAtColumn()
	{
		return isset($this->createdAtColumn) ? $this->createdAtColumn : 'created_at';
	}

	/**
	 * Returns the column that holds the "updated at" timestamp.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getUpdatedAtColumn()
	{
		return isset($this->updatedAtColumn) ? $this->updatedAtColumn : 'updated_at';
	}

	/**
	 * Returns the columns that we're casting.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function getCastColumns()
	{
		return $this->cast + [$this->getCreatedAtColumn() => 'date', $this->getUpdatedAtColumn() => 'date'];
	}

	/**
	 * Allows you to update the "updated at" timestamp without modifying any data.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function touch()
	{
		if($this->exists)
		{
			$this->columns[$this->getUpdatedAtColumn()] = null;

			return $this->save();
		}

		return false;
	}

	/**
	 * Touches related records.
	 *
	 * @access  protected
	 */

	protected function touchRelated()
	{
		if(!empty($this->touch))
		{
			foreach($this->touch as $touch)
			{
				$touch = explode('.', $touch);

				$relation = $this->{array_shift($touch)}();

				foreach($touch as $nested)
				{
					$related = $relation->first();

					if($related === false)
					{
						continue 2;
					}

					$relation = $related->$nested();
				}

				$relation->update([$relation->getModel()->getUpdatedAtColumn() => null]);
			}
		}
	}
}