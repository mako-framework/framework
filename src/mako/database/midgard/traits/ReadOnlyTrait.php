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
				function($values, $query)
				{
					throw new ReadOnlyException(vsprintf("%s(): Attempted to create a read-only record.", [__METHOD__]));
				},
			],
			'beforeUpdate' =>
			[
				function($values, $query)
				{
					throw new ReadOnlyException(vsprintf("%s(): Attempted to update a read-only record.", [__METHOD__]));
				},
			],
			'beforeDelete' =>
			[
				function($query)
				{
					throw new ReadOnlyException(vsprintf("%s(): Attempted to delete a read-only record.", [__METHOD__]));
				},
			],
		];
	}
}
