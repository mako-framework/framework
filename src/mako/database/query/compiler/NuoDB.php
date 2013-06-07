<?php

namespace mako\database\query\compiler;

/**
 * Compiles NuoDB queries.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class NuoDB extends \mako\database\query\Compiler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Wrapper used to escape table and column names.
	 *
	 * @var string
	 */
	
	protected $wrapper = '"%s"';

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Compiles LIMIT clauses.
	 *
	 * @access  protected
	 * @param   int        $limit  Limit
	 * @return  string
	 */

	protected function limit($limit)
	{
		return ($limit === null) ? '' : ' FETCH ' . $limit;
	}

	/**
	 * Compiles OFFSET clauses.
	 *
	 * @access  protected
	 * @param   int        $offset  Limit
	 * @return  string
	 */

	protected function offset($offset)
	{
		return ($offset === null) ? '' : ' OFFSET ' . $offset;
	}
}

/** -------------------- End of file -------------------- **/