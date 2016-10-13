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
	protected function getTimestampedTraitHooks(): array
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
					if($this->shouldTouchOnInsert() && $inserted && $this->exists)
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
					if($this->shouldTouchOnUpdate() && $updated > 0 && $this->exists)
					{
						$this->touchRelated();
					}
				}
			],
			'afterDelete' =>
			[
				function($deleted)
				{
					if($this->shouldTouchOnDelete() && $deleted > 0 && $this->exists)
					{
						$this->touchRelated();
					}
				}
			],
		];
	}

	/**
	 * Should we touch relations on insert?
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function shouldTouchOnInsert(): bool
	{
		return true;
	}

	/**
	 * Should we touch relations on update?
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function shouldTouchOnUpdate(): bool
	{
		return true;
	}

	/**
	 * Should we touch relations on delete?
	 *
	 * @access  protected
	 * @return  bool
	 */
	protected function shouldTouchOnDelete(): bool
	{
		return true;
	}

	/**
	 * Returns the column that holds the "created at" timestamp.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getCreatedAtColumn(): string
	{
		return isset($this->createdAtColumn) ? $this->createdAtColumn : 'created_at';
	}

	/**
	 * Returns the column that holds the "updated at" timestamp.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getUpdatedAtColumn(): string
	{
		return isset($this->updatedAtColumn) ? $this->updatedAtColumn : 'updated_at';
	}

	/**
	 * Returns the columns that we're casting.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function getCastColumns(): array
	{
		return $this->cast + [$this->getCreatedAtColumn() => 'date', $this->getUpdatedAtColumn() => 'date'];
	}

	/**
	 * Allows you to update the "updated at" timestamp without modifying any data.
	 *
	 * @access  public
	 * @return  bool
	 */
	public function touch(): bool
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