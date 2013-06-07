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

	/**
	 * Wrapper used to escape table and column names.
	 *
	 * @var string
	 */
	
	protected $wrapper = '`%s`';

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	// Nothing here
}

/** -------------------- End of file -------------------- **/