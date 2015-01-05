<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Compiler;

/**
 * Compiles NuoDB queries.
 *
 * @author  Frederic G. Østby
 */

class NuoDB extends Compiler
{
	/**
	 * {@inheritdoc}
	 */

	public function escapeIdentifier($identifier)
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}
}