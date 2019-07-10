<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use function trim;

/**
 * Compiles DB2 queries.
 *
 * @author Frederic G. Østby
 */
class DB2 extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock): string
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE WITH RS' : ($lock === false ? ' FOR READ ONLY WITH RS' : " {$lock}");
	}

	/**
	 * {@inheritdoc}
	 */
	public function select(): array
	{
		if($this->query->getLimit() === null)
		{
			// No limit so we can just execute a normal query

			return parent::select();
		}

		// There is a limit so we need to emulate the LIMIT/OFFSET clause with ANSI-SQL

		$order = trim($this->orderings($this->query->getOrderings()));

		if(empty($order))
		{
			$order = 'ORDER BY (SELECT 0)';
		}

		$sql  = $this->query->getPrefix();
		$sql .= $this->setOperations($this->query->getSetOperations());
		$sql .= $this->query->isDistinct() ? 'SELECT DISTINCT ' : 'SELECT ';
		$sql .= $this->columns($this->query->getColumns());
		$sql .= ", ROW_NUMBER() OVER ({$order}) AS mako_rownum";
		$sql .= $this->from($this->query->getTable());
		$sql .= $this->joins($this->query->getJoins());
		$sql .= $this->wheres($this->query->getWheres());
		$sql .= $this->groupings($this->query->getGroupings());
		$sql .= $this->havings($this->query->getHavings());

		$offset = ($this->query->getOffset() === null) ? 0 : $this->query->getOffset();

		$limit  = $offset + $this->query->getLimit();
		$offset = $offset + 1;

		$sql  = "SELECT * FROM ({$sql}) AS mako1 WHERE mako_rownum BETWEEN {$offset} AND {$limit}";
		$sql .= $this->lock($this->query->getLock());

		return ['sql' => $sql, 'params' => $this->params];
	}
}
