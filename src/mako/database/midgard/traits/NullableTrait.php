<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use function in_array;
use function property_exists;

/**
 * Nullable trait.
 */
trait NullableTrait
{
	/**
	 * Returns array of nullable columns.
	 */
	protected function getNullableColumns(): array
	{
		return property_exists($this, 'nullable') ? $this->nullable : [];
	}

	/**
	 * Will replace empty strings with null if the column is nullable.
	 */
	protected function setEmptyNullablesToNull(array $values): array
	{
		$nullables = $this->getNullableColumns();

		foreach ($values as $column => $value) {
			if ($value === '' && in_array($column, $nullables)) {
				$values[$column] = null;
			}
		}

		return $values;
	}

	/**
	 * Returns trait hooks.
	 */
	protected function getNullableTraitHooks(): array
	{
		return [
			'beforeInsert' => [
				fn ($values, $query): array => $this->setEmptyNullablesToNull($values),
			],
			'beforeUpdate' => [
				fn ($values, $query): array => $this->setEmptyNullablesToNull($values),
			],
		];
	}
}
