<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use function str_replace;

/**
 * Compiles NuoDB queries.
 */
class NuoDB extends Compiler
{
	/**
	 * {@inheritDoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritDoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * {@inheritDoc}
	 */
	public function lock(bool|string|null $lock): string
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' LOCK IN SHARE MODE' : " {$lock}");
	}
}
