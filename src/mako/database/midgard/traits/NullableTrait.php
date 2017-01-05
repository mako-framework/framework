<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

/**
 * Nullable trait.
 *
 * @author Frederic G. Østby
 */
trait NullableTrait
{
	/**
	 * Returns array of nullable columns.
	 *
	 * @access protected
	 * @return array
	 */
	protected function getNullableColumns(): array
	{
		return property_exists($this, 'nullable') ? $this->nullable : [];
	}

	/**
	 * Will replace empty strings with null if the column is nullable.
	 *
	 * @access protected
	 * @param  array $values Values
	 * @return array
	 */
	protected function setEmptyNullablesToNull(array $values): array
	{
		$nullables = $this->getNullableColumns();

		foreach($values as $column => $value)
		{
			if($value === '' && in_array($column, $nullables))
			{
				$values[$column] = null;
			}
		}

		return $values;
	}

	/**
	 * Returns trait hooks.
	 *
	 * @access protected
	 * @return array
	 */
	protected function getNullableTraitHooks(): array
	{
		return
		[
			'beforeInsert' =>
			[
				function($values, $query): array
				{
					return $this->setEmptyNullablesToNull($values);
				},
			],
			'beforeUpdate' =>
			[
				function($values, $query): array
				{
					return $this->setEmptyNullablesToNull($values);
				},
			],
		];
	}
}
