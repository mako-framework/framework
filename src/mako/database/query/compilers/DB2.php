<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Compiler;

/**
 * Compiles DB2 queries.
 *
 * @author  Frederic G. Østby
 */

class DB2 extends Compiler
{
	/**
	 * {@inheritdoc}
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
			// There is a limit so we need to emulate the LIMIT/OFFSET clause with ANSI-SQL

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

			$offset = ($this->query->getOffset() === null) ? 0 : $this->query->getOffset();

			$limit  = $offset + $this->query->getLimit();
			$offset = $offset + 1;

			$sql = 'SELECT * FROM (' . $sql . ') AS mako1 WHERE mako_rownum BETWEEN ' . $offset . ' AND ' . $limit;

			return ['sql' => $sql, 'params' => $this->params];
		}
	}
}