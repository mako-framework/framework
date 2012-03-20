<?php

namespace mako\database\query\compiler;

/**
* Compiles Firebird queries.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Firebird extends \mako\database\query\Compiler
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
	* @param   int        Limit
	* @return  string
	*/

	protected function limit($limit, $offset = null)
	{
		return empty($limit) ? '' : ' TO ' . ($limit + (empty($offset) ? 0 : $offset));
	}

	/**
	* Compiles OFFSET clause.
	*
	* @access  protected
	* @param   int        Offset
	* @return  string
	*/

	protected function offset($offset, $limit = null)
	{
		return empty($offset) ? empty($limit) ? '' :' ROWS 1 ' : ' ROWS ' . $offset;
	}

	/**
	* Compiles a SELECT query.
	*
	* @access  public
	* @return  string
	*/

	public function select()
	{
		$sql  = $this->query->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $this->columns($this->query->columns);
		$sql .= ' FROM ';
		$sql .= $this->wrap($this->query->table);
		$sql .= $this->joins($this->query->joins);
		$sql .= $this->wheres($this->query->wheres);
		$sql .= $this->groupings($this->query->groupings);
		$sql .= $this->orderings($this->query->orderings);
		$sql .= $this->havings($this->query->havings);
		$sql .= $this->offset($this->query->offset, $this->query->limit);
		$sql .= $this->limit($this->query->limit, $this->query->offset);

		return array('sql' => $sql, 'params' => $this->params);
	}
}

/** -------------------- End of file --------------------**/