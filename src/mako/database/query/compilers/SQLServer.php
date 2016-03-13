<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

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
	protected function buildJsonPath($column, array $segments)
	{
		$path = '';

		foreach($segments as $segment)
		{
			if(is_numeric($segment))
			{
				$path .= '[' . $segment . ']';
			}
			else
			{
				$path .= '.' . $segment;
			}
		}

		return 'json_value(' . $column . ', ' . "'lax $" . str_replace("'", "''", $path) . "'" . ')';
	}

	/**
	 * {@inheritdoc}
	 */
	public function from($from)
	{
		$from = parent::from($from);

		if(($lock = $this->query->getLock()) !== null)
		{
			$from .= $lock === true ? ' WITH (UPDLOCK, ROWLOCK)' : ($lock === false ? ' WITH (HOLDLOCK, ROWLOCK)' : ' ' . $lock);
		}

		return $from;
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
				$sql .= $this->from($this->query->getTable());
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
				$sql .= $this->from($this->query->getTable());
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