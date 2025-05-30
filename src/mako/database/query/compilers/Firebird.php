<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Raw;
use mako\database\query\Subquery;

/**
 * Compiles Firebird queries.
 */
class Firebird extends Compiler
{
	/**
	 * {@inheritDoc}
	 */
	protected static string $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritDoc}
	 */
	protected function from(null|array|Raw|string|Subquery $table): string
	{
		return $table === null ? ' FROM RDB$DATABASE' : parent::from($table);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function betweenDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE)" . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->param($where['value1'])} AND {$this->param($where['value2'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function whereDate(array $where): string
	{
		return "CAST({$this->columnName($where['column'])} AS DATE) {$where['operator']} {$this->param($where['value'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	protected function limit(?int $limit): string
	{
		$offset = $this->query->getOffset();

		return ($offset === null) ? (($limit === null) ? '' : ' ROWS 1') : ' ROWS ' . ($offset + 1);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function offset(?int $offset): string
	{
		$limit = $this->query->getLimit();

		return ($limit === null) ? '' : ' TO ' . ($limit + (($offset === null) ? 0 : $offset));
	}

	/**
	 * {@inheritDoc}
	 */
	public function lock(null|bool|string $lock): string
	{
		if ($lock === null) {
			return '';
		}

		return $lock === true ? ' FOR UPDATE WITH LOCK' : ($lock === false ? ' WITH LOCK' : " {$lock}");
	}

	/**
	 * {@inheritDoc}
	 */
	public function insertAndReturn(array $values, array $return): array
	{
		['sql' => $sql, 'params' => $params] = $this->insert($values);

		$sql .= " RETURNING {$this->columnNames($return)}";

		return ['sql' => $sql, 'params' => $params];
	}

	/**
	 * {@inheritDoc}
	 */
	public function insertMultipleAndReturn(array $return, array ...$values): array
	{
		['sql' => $sql, 'params' => $params] = $this->insertMultiple(...$values);

		$sql .= " RETURNING {$this->columnNames($return)}";

		return ['sql' => $sql, 'params' => $params];
	}

	/**
	 * {@inheritDoc}
	 */
	public function updateAndReturn(array $values, array $return): array
	{
		$query = $this->update($values);

		$query['sql'] .= " RETURNING {$this->columnNames($return)}";

		return $query;
	}
}
