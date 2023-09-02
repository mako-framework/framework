<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use DateTime;

use function array_shift;
use function explode;
use function property_exists;

/**
 * Timestamped trait.
 */
trait TimestampedTrait
{
	/**
	 * Returns trait hooks.
	 */
	protected function getTimestampedTraitHooks(): array
	{
		return
		[
			'beforeInsert' =>
			[
				function (array $values, $query): array
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
				function ($inserted): void
				{
					if($this->shouldTouchOnInsert() && $inserted && $this->isPersisted)
					{
						$this->touchRelated();
					}
				},
			],
			'beforeUpdate' =>
			[
				function (array $values, $query): array
				{
					$dateTime = new DateTime;

					$updatedAtColumn = $this->getUpdatedAtColumn();

					$this->columns[$updatedAtColumn] = $dateTime;

					return [$updatedAtColumn => $dateTime] + $values;
				},
			],
			'afterUpdate' =>
			[
				function ($updated): void
				{
					if($this->shouldTouchOnUpdate() && $updated > 0 && $this->isPersisted)
					{
						$this->touchRelated();
					}
				},
			],
			'afterDelete' =>
			[
				function ($deleted): void
				{
					if($this->shouldTouchOnDelete() && $deleted > 0 && $this->isPersisted)
					{
						$this->touchRelated();
					}
				},
			],
		];
	}

	/**
	 * Returns trait casts.
	 */
	protected function getTimestampedTraitCasts(): array
	{
		return [$this->getCreatedAtColumn() => 'date', $this->getUpdatedAtColumn() => 'date'];
	}

	/**
	 * Should we touch relations on insert?
	 */
	protected function shouldTouchOnInsert(): bool
	{
		return property_exists($this, 'shouldTouchOnInsert') ? $this->shouldTouchOnInsert : true;
	}

	/**
	 * Should we touch relations on update?
	 */
	protected function shouldTouchOnUpdate(): bool
	{
		return property_exists($this, 'shouldTouchOnUpdate') ? $this->shouldTouchOnUpdate : true;
	}

	/**
	 * Should we touch relations on delete?
	 */
	protected function shouldTouchOnDelete(): bool
	{
		return property_exists($this, 'shouldTouchOnDelete') ? $this->shouldTouchOnDelete : true;
	}

	/**
	 * Returns the column that holds the "created at" timestamp.
	 */
	public function getCreatedAtColumn(): string
	{
		return property_exists($this, 'createdAtColumn') ? $this->createdAtColumn : 'created_at';
	}

	/**
	 * Returns the column that holds the "updated at" timestamp.
	 */
	public function getUpdatedAtColumn(): string
	{
		return property_exists($this, 'updatedAtColumn') ? $this->updatedAtColumn : 'updated_at';
	}

	/**
	 * Returns the relations that we should touch.
	 */
	protected function getRelationsToTouch(): array
	{
		return property_exists($this, 'touch') ? $this->touch : [];
	}

	/**
	 * Allows you to update the "updated at" timestamp without modifying any data.
	 */
	public function touch(): bool
	{
		if($this->isPersisted)
		{
			$this->columns[$this->getUpdatedAtColumn()] = null;

			return $this->save();
		}

		return false;
	}

	/**
	 * Touches related records.
	 */
	protected function touchRelated(): void
	{
		foreach($this->getRelationsToTouch() as $touch)
		{
			$touch = explode('.', $touch);

			$relation = $this->{array_shift($touch)}();

			foreach($touch as $nested)
			{
				$related = $relation->first();

				if($related === null)
				{
					continue 2;
				}

				$relation = $related->$nested();
			}

			$relation->update([$relation->getModel()->getUpdatedAtColumn() => null]);
		}
	}
}
