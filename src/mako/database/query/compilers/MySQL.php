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