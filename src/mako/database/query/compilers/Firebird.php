<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Compiler;

/**
 * Compiles Firebird queries.
 *
 * @author  Frederic G. Østby
 */

class Firebird extends Compiler
{
	/**
	 * {@inheritdoc}
	 */

	protected function limit($limit, $offset = null)
	{
		return ($limit === null) ? '' : ' TO ' . ($limit + (($offset === null) ? 0 : $offset));
	}

	/**
	 * {@inheritdoc}
	 */

	protected function offset($offset, $limit = null)
	{
		return ($offset === null) ? ($limit === null) ? '' :' ROWS 1 ' : ' ROWS ' . ($offset + 1);
	}

	/**
	 * {@inheritdoc}
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
		$sql .= $this->havings($this->query->getHavings());
		$sql .= $this->orderings($this->query->getOrderings());
		$sql .= $this->offset($this->query->getOffset(), $this->query->getLimit());
		$sql .= $this->limit($this->query->getLimit(), $this->query->getOffset());

		return ['sql' => $sql, 'params' => $this->params];
	}
}