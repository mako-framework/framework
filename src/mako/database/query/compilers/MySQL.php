<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

/**
 * Compiles MySQL queries.
 *
 * @author  Frederic G. Østby
 */

class MySQL extends \mako\database\query\Compiler
{
	/**
	 * Returns an escaped identifier.
	 * 
	 * @access  public
	 * @param   string  $identifier  Identifier to escape
	 * @return  string
	 */

	public function escapeIdentifier($identifier)
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}
}