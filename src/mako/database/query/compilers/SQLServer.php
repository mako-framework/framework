<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Compiler;

/**
 * Compiles SQL Server queries.
 *
 * @author  Frederic G. Østby
 */

class SQLServer extends Compiler
{
	/**
	 * Date format.
	 *
	 * @var string
	 */

	protected static $dateForamt = 'Y-m-d H:i:s.0000000';

	/**
	 * {@inheritdoc}
	 */

	public function escapeIdentifier($identifier)
	{
		return '[' . str_replace(']', ']]', $identifier) . ']';
	}

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
				$sql .= $this->havings($this->query->getHavings());
				$sql .= $this->orderings($this->query->getOrderings());
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

				$sql = 'SELECT * FROM (' . $sql . ') AS mako1 WHERE mako_rownum BETWEEN ' . $offset . ' AND ' . $limit;
			}

			return ['sql' => $sql, 'params' => $this->params];
		}
	}
}