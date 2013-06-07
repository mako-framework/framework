<?php

namespace mako\database\query\compiler;

/**
 * Compiles SQL Server queries.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class SQLServer extends \mako\database\query\Compiler
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Wrapper used to escape table and column names.
	 *
	 * @var string
	 */
	
	protected $wrapper = '[%s]';

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

			if($this->query->getOffset() === null)
			{
				// No offset so we can just use the TOP clause

				$sql  = $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
				$sql .= 'TOP ' . $this->query->getLimit() . ' ';
				$sql .= $this->columns($this->query->getColumns());
				$sql .= ' FROM ';
				$sql .= $this->wrap($this->query->getTable());
				$sql .= $this->joins($this->query->getJoins());
				$sql .= $this->wheres($this->query->getWheres());
				$sql .= $this->groupings($this->query->getGroupings());
				$sql .= $this->orderings($this->query->getOrderings());
				$sql .= $this->havings($this->query->getHavings());
			}
			else
			{
				// There is an offset so we need to emulate the OFFSET clause with ANSI-SQL

				$order = trim($this->orderings($this->query->getOrderings()));

				if(empty($order))
				{
					$order = 'ORDER BY (SELECT 0)';
				}

				$sql  = $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
				$sql .= $this->columns($this->query->getColumns());
				$sql .= ', ROW_NUMBER() OVER (' . $order . ') AS mako_rownum';
				$sql .= ' FROM ';
				$sql .= $this->wrap($this->query->getTable());
				$sql .= $this->joins($this->query->getJoins());
				$sql .= $this->wheres($this->query->getWheres());
				$sql .= $this->groupings($this->query->getGroupings());
				$sql .= $this->havings($this->query->getHavings());

				$limit  = $this->query->getOffset() + $this->query->getLimit();
				$offset = $this->query->getOffset() + 1;

				$sql = 'SELECT * FROM (' . $sql . ') AS m1 WHERE mako_rownum BETWEEN ' . $offset . ' AND ' . $limit;
			}

			return array('sql' => $sql, 'params' => $this->params);
		}
	}
}

/** -------------------- End of file -------------------- **/