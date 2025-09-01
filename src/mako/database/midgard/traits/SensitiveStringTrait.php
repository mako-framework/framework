<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\types\SensitiveString;

use function in_array;
use function property_exists;

/**
 * Sensitive string trait.
 */
trait SensitiveStringTrait
{
	/**
	 * Returns array of sensitive string columns.
	 */
	protected function getSensitiveStringColumns(): array
	{
		return property_exists($this, 'sensitiveStrings') ? $this->sensitiveStrings : [];
	}

	/**
	 * Will replace sensitive strings with a SensitiveString instance if the column is marked as sensitive.
	 */
	protected function encapsulateSensitiveStrings(array $values): array
	{
		$sensitiveStrings = $this->getSensitiveStringColumns();

		foreach ($values as $column => $value) {
			if (!empty($value) && in_array($column, $sensitiveStrings)) {
				$values[$column] = new SensitiveString($value);
			}
		}

		return $values;
	}

	/**
	 * Returns trait hooks.
	 */
	protected function getSensitiveStringTraitHooks(): array
	{
		return [
			'beforeInsert' => [
				fn ($values, $query): array => $this->encapsulateSensitiveStrings($values),
			],
			'beforeUpdate' => [
				fn ($values, $query): array => $this->encapsulateSensitiveStrings($values),
			],
		];
	}
}
