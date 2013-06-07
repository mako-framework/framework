<?php

namespace mako\database\query\compiler;

/**
 * Compiles Firebird queries.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Firebird extends \mako\database\query\Compiler
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
	 * Compiles LIMIT clauses.
	 *
	 * @access  protected
	 * @param   int        $limit   Limit
	 * @param   int        $offset  Offset
	 * @return  string
	 */

	protected function limit($limit, $offset = null)
	{
		return ($limit === null) ? '' : ' TO ' . ($limit + (($offset === null) ? 0 : $offset));
	}

	/**
	 * Compiles OFFSET clause.
	 *
	 * @access  protected
	 * @param   int        $limit   Offset
	 * @param   int        $offset  Limit
	 * @return  string
	 */

	protected function offset($offset, $limit = null)
	{
		return ($offset === null) ? ($limit === null) ? '' :' ROWS 1 ' : ' ROWS ' . ($offset + 1);
	}

	/**
	 * Compiles a SELECT query.
	 *
	 * @access  public
	 * @return  array
	 */

	public function select()
	{
		$sql  = $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $this->columns($this->query->getColumns());
		$sql .= ' FROM ';
		$sql .= $this->wrap($this->query->getTable());
		$sql .= $this->joins($this->query->getJoins());
		$sql .= $this->wheres($this->query->getWheres());
		$sql .= $this->groupings($this->query->getGroupings());
		$sql .= $this->orderings($this->query->getOrderings());
		$sql .= $this->havings($this->query->getHavings());
		$sql .= $this->offset($this->query->getOffset(), $this->query->getLimit());
		$sql .= $this->limit($this->query->getLimit(), $this->query->getOffset());

		return array('sql' => $sql, 'params' => $this->params);
	}
}

/** -------------------- End of file -------------------- **/