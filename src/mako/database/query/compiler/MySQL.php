<?php

namespace mako\database\query\compiler;

/**
 * Compiles MySQL queries.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class MySQL extends \mako\database\query\Compiler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here
	
	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

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

/** -------------------- End of file -------------------- **/