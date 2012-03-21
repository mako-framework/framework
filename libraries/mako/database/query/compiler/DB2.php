<?php

namespace mako\database\query\compiler;

/**
* Compiles DB2 queries.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class DB2 extends \mako\database\query\Compiler
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
			// There is a limit so we need to emulate the LIMIT/OFFSET clause with ANSI-SQL

			$order = trim($this->orderings($this->query->orderings));

			if(empty($order))
			{
				$order = 'ORDER BY (SELECT 0)';
			}

			$sql  = $this->query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
			$sql .= $this->columns($this->query->columns);
			$sql .= ', ROW_NUMBER() OVER (' . $order . ') AS mako_rownum';
			$sql .= ' FROM ';
			$sql .= $this->wrap($this->query->table);
			$sql .= $this->joins($this->query->joins);
			$sql .= $this->wheres($this->query->wheres);
			$sql .= $this->groupings($this->query->groupings);
			$sql .= $this->havings($this->query->havings);

			$offset = empty($this->query->offset) ? 0 : $this->query->offset;

			$limit  = $offset + $this->query->limit;
			$offset = $offset + 1;

			$sql = 'SELECT * FROM (' . $sql . ') AS m1 WHERE mako_rownum BETWEEN ' . $offset . ' AND ' . $limit;

			return array('sql' => $sql, 'params' => $this->params);
		}
	}
}

/** -------------------- End of file --------------------**/