<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;
use Override;

/**
 * Compiles SQLite queries.
 */
class SQLite extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected static string $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "JSON_EXTRACT({$column}, '{$this->buildJsonPath($segments)}')";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . " = JSON_SET({$column}, '{$this->buildJsonPath($segments)}', JSON({$param}))";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function betweenDate(array $where): string
	{
		$date1 = "{$where['value1']} 00:00:00.000";
		$date2 = "{$where['value2']} 23:59:59.999";

		return $this->columnName($where['column']) . ($where['not'] ? ' NOT BETWEEN ' : ' BETWEEN ') . "{$this->simpleParam($date1)} AND {$this->simpleParam($date2)}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function whereDate(array $where): string
	{
		switch ($where['operator']) {
			case '=':
			case '!=':
			case '<>':
				$where = [
					'column' => $where['column'],
					'not'    => $where['operator'] !== '=',
					'value1' => $where['value'],
					'value2' => $where['value'],
				];

				return $this->betweenDate($where);
			case '>':
			case '>=':
			case '<':
			case '<=':
				switch ($where['operator']) {
					case '>=':
					case '<':
						$suffix = ' 00:00:00.000';
						break;
					default:
						$suffix = ' 23:59:59.999';
				}

				return "{$this->columnName($where['column'])} {$where['operator']} {$this->simpleParam("{$where['value']}{$suffix}")}";
			default:
				return "strftime('%Y-%m-%d', {$this->columnName($where['column'])}) {$where['operator']} {$this->simpleParam($where['value'])}";
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function offset(?int $offset): string
	{
		if ($offset === null) {
			return '';
		}

		return ($this->query->getLimit() === null) ? " LIMIT -1 OFFSET {$offset}" : " OFFSET {$offset}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function insertAndReturn(array $values, array $return): array
	{
		['sql' => $sql, 'params' => $params] = $this->insert($values);

		$sql .= " RETURNING {$this->columnNames($return)}";

		return ['sql' => $sql, 'params' => $params];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function insertMultipleAndReturn(array $return, array ...$values): array
	{
		['sql' => $sql, 'params' => $params] = $this->insertMultiple(...$values);

		$sql .= " RETURNING {$this->columnNames($return)}";

		return ['sql' => $sql, 'params' => $params];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function insertOrUpdate(array $insertValues, array $updateValues, array $conflictTarget = []): array
	{
		$sql = $sql = $this->query->getPrefix()
		. $this->insertWithValues($insertValues)
		. " ON CONFLICT ({$this->escapeIdentifiers($conflictTarget)}) DO UPDATE SET "
		. $this->updateColumns($updateValues);

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function updateAndReturn(array $values, array $return): array
	{
		$query = $this->update($values);

		$query['sql'] .= " RETURNING {$this->columnNames($return)}";

		return $query;
	}
}
