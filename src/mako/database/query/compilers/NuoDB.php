<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

/**
 * Compiles NuoDB queries.
 *
 * @author  Frederic G. Østby
 */

class NuoDB extends \mako\database\query\Compiler
{
	/**
	 * Returns an escaped identifier.
	 * 
	 * @access  protected
	 * @param   string     $identifier  Identifier to escape
	 * @return  string
	 */

	protected function escapeIdentifier($identifier)
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}
}