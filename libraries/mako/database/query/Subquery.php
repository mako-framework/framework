<?php

namespace mako\database\query;

use \mako\database\Query;

/**
* Subquery container.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Subquery
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Query builder.
	*
	* @var mako\database\Query
	*/

	protected $query;

	/**
	* Alias.
	*
	* @var string
	*/

	protected $alias = null;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  public
	* @param   mako\database\Query  Query builder
	* @param   string               Subquery alias
	*/

	public function __construct(Query $query, $alias = null)
	{
		$this->query = $query;
		$this->alias = $alias;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Returns the compiled query.
	*
	* @access  public
	* @return  array
	*/

	public function get()
	{
		$query = $this->query->compiler->select();

		$query['sql'] = '(' . $query['sql'] . ')';

		if($this->alias !== null)
		{
			$query['sql'] .= ' AS ' . $this->query->compiler->wrap($this->alias);
		}

		return $query;
	}
}

/** -------------------- End of file --------------------**/