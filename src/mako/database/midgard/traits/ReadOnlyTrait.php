<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\midgard\traits\exceptions\ReadOnlyException;

/**
 * Read-only trait.
 *
 * @author Frederic G. Østby
 */
trait ReadOnlyTrait
{
	/**
	 * Returns trait hooks.
	 *
	 * @return array
	 */
	protected function getReadOnlyTraitHooks(): array
	{
		return
		[
			'beforeInsert' =>
			[
				function($values, $query): void
				{
					throw new ReadOnlyException('Attempted to create a read-only record.');
				},
			],
			'beforeUpdate' =>
			[
				function($values, $query): void
				{
					throw new ReadOnlyException('Attempted to update a read-only record.');
				},
			],
			'beforeDelete' =>
			[
				function($query): void
				{
					throw new ReadOnlyException('Attempted to delete a read-only record.');
				},
			],
		];
	}
}
