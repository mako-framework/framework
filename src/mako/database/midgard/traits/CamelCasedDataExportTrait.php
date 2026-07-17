<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\midgard\ORM;
use mako\utility\Str;

/**
 * Camel cased data export trait.
 *
 * @phpstan-require-extends ORM
 */
trait CamelCasedDataExportTrait
{
	/**
	 * Returns an array representation of the record.
	 */
	public function toArray(): array
	{
		$camelCased = [];

		$array = parent::toArray();

		foreach ($array as $key => $value) {
			$camelCased[Str::snakeToCamel($key)] = $value;
		}

		return $camelCased;
	}
}
