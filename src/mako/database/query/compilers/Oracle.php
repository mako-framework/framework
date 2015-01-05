<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Compiler;

/**
 * Compiles Oracle queries.
 *
 * @author  Frederic G. Østby
 */

class Oracle extends Compiler
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
			$sql  = $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
			$sql .= $this->columns($this->query->getColumns());
			$sql .= ' FROM ';
			$sql .= $this->wrap($this->query->getTable());
			$sql .= $this->joins($this->query->getJoins());
			$sql .= $this->wheres($this->query->getWheres());
			$sql .= $this->groupings($this->query->getGroupings());
			$sql .= $this->havings($this->query->getHavings());
			$sql .= $this->orderings($this->query->getOrderings());

			if($this->query->getOffset() === null)
			{
				// No offset so we only need a simple subquery to emulate the LIMIT clause

				$sql = 'SELECT mako1.* FROM (' . $sql . ') mako1 WHERE rownum <= ' . $this->query->getLimit();
			}
			else
			{
				// There is an offset so we need to make a bunch of subqueries to emulate the LIMIT and OFFSET clauses

				$limit  = $this->query->getLimit() + $this->query->getOffset();
				$offset = $this->query->getOffset() + 1;

				$sql = 'SELECT * FROM (SELECT mako1.*, rownum AS mako_rownum FROM (' . $sql . ') mako1 WHERE rownum <= ' . $limit . ') WHERE mako_rownum >= ' . $offset;
			}

			return ['sql' => $sql, 'params' => $this->params];
		}
	}
}