<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles MySQL queries.
 *
 * @author  Frederic G. Østby
 */
class MySQL extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	public function escapeIdentifier($identifier)
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonPath($column, array $segments)
	{
		$path = '';

		foreach($segments as $segment)
		{
			if(is_numeric($segment))
			{
				$path .= '[' . $segment . ']';
			}
			else
			{
				$path .= '.' . $segment;
			}
		}

		return $column . '->"$' . str_replace('"', '""', $path) . '"';
	}

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock)
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' LOCK IN SHARE MODE' : ' ' . $lock);
	}
}