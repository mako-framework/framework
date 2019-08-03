<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use function str_replace;

/**
 * Compiles NuoDB queries.
 *
 * @author Frederic G. Østby
 */
class NuoDB extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock): string
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' LOCK IN SHARE MODE' : " {$lock}");
	}
}
