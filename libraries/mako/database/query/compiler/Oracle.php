<?php

namespace mako\database\query\compiler;

/**
* Compiles Oracle queries.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Oracle extends \mako\database\query\Compiler
{
	//---------------------------------------------
	// Class variables
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
	* Compiles LIMIT clauses.
	*
	* @access  protected
	* @return  string
	*/

	protected function limit($limit)
	{
		return '';
	}

	/**
	* Compiles OFFSET clause.
	*
	* @access  protected
	* @return  string
	*/

	protected function offset($offset)
	{
		return '';
	}

	/**
	* Compiles a SELECT query.
	*
	* @access  public
	* @return  string
	*/

	public function select()
	{
		if(empty($this->query->limit))
		{
			// No limit so we can just execute a normal query

			return parent::select();
		}
		else
		{
			// Oracle doesn't support the LIMIT/OFFSET syntax 
			// so we need to do some changes to the query to achieve the same result

			$query = parent::select();

			$limit = $this->query->limit;

			$offset = empty($this->query->offset) ? 0 : $this->query->offset;

			$sql = 'SELECT m2.* FROM (SELECT m1.*, ROWNUM AS "mako_rownum" FROM (' . $query['sql'] . ') m1) m2 WHERE m2."mako_rownum" BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $limit);

			return array('sql' => $sql, 'params' => $query['params']);
		}
	}
}

/** -------------------- End of file --------------------**/