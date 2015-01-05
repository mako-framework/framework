<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Compiler;

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
}