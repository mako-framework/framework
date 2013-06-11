<?php

namespace mako\database\query\compiler;

/**
 * Compiles Oracle queries.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Oracle extends \mako\database\query\Compiler
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
	 * Compiles a SELECT query.
	 *
	 * @access  public
	 * @return  array
	 */

	public function select()
	{
		if($this->query->getLimit() === null)
		{
			// No limit so we can just execute a normal query

			return parent::select();
		}
		else
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

			if($this->query->getOffset() === null)
			{
				// No offset so we only need a simple subquery to emulate the LIMIT clause

				$sql = 'SELECT m1.* FROM (' . $sql . ') m1 WHERE rownum <= ' . $this->query->getLimit();
			}
			else
			{
				// There is an offset so we need to make a bunch of subqueries to emulate the LIMIT and OFFSET clauses

				$limit  = $this->query->getLimit() + $this->query->getOffset();
				$offset = $this->query->getOffset() + 1;

				$sql = 'SELECT * FROM (SELECT m1.*, rownum AS mako_rownum FROM (' . $sql . ') m1 WHERE rownum <= ' . $limit . ') WHERE mako_rownum >= ' . $offset;
			}

			return array('sql' => $sql, 'params' => $this->params);
		}
	}
}

/** -------------------- End of file -------------------- **/