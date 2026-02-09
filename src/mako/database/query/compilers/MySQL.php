<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;
use mako\database\query\VectorMetric;
use Override;

use function str_replace;

/**
 * Compiles MySQL queries.
 */
class MySQL extends Compiler
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
	public function escapeIdentifier(string $identifier): string
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonGet(string $column, array $segments): string
	{
		return "{$column}->>'{$this->buildJsonPath($segments)}'";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return "{$column} = JSON_SET({$column}, '{$this->buildJsonPath($segments)}', CAST({$param} AS JSON))";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function whereVectorDistance(array $where): string
	{
		$vector = is_array($where['vector']) ? json_encode($where['vector']) : $where['vector'];

		$metric = match ($where['metric']) {
			VectorMetric::COSINE => 'COSINE',
			VectorMetric::EUCLIDEAN => 'EUCLIDEAN',
		};

		return "DISTANCE({$this->column($where['column'], false)}, STRING_TO_VECTOR({$this->param($vector)}), '{$metric}') <= {$this->param($where['distance'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function betweenDate(array $where): string
	{
		$date1 = "{$where['value1']} 00:00:00.000000";
		$date2 = "{$where['value2']} 23:59:59.999999";

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
						$suffix = ' 00:00:00.000000';
						break;
					default:
						$suffix = ' 23:59:59.999999';
				}

				return "{$this->columnName($where['column'])} {$where['operator']} {$this->simpleParam("{$where['value']}{$suffix}")}";
			default:
				return "DATE({$this->columnName($where['column'])}) {$where['operator']} {$this->simpleParam($where['value'])}";
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

		return ($this->query->getLimit() === null) ? " LIMIT 18446744073709551615 OFFSET {$offset}" : " OFFSET {$offset}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function lock(null|bool|string $lock): string
	{
		if ($lock === null) {
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' LOCK IN SHARE MODE' : " {$lock}");
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function insertWithoutValues(): string
	{
		return "INSERT INTO {$this->escapeTableName($this->query->getTable())} () VALUES ()";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function insertOrUpdate(array $insertValues, array $updateValues, array $conflictTarget = []): array
	{
		$sql = $this->query->getPrefix()
		. $this->insertWithValues($insertValues)
		. ' ON DUPLICATE KEY UPDATE '
		. $this->updateColumns($updateValues);

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function update(array $values): array
	{
		$sql = $this->query->getPrefix()
		. 'UPDATE '
		. $this->escapeTableNameWithAlias($this->query->getTable())
		. $this->joins($this->query->getJoins())
		. ' SET '
		. $this->updateColumns($values)
		. $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function delete(array $tables = []): array
	{
		$sql = $this->query->getPrefix()
		. 'DELETE ';

		if (!empty($this->query->getJoins())) {
			if (empty($tables)) {
				$sql .= "{$this->escapeTableName($this->query->getTable())} ";
			}
			else {
				$sql .=  "{$this->escapeTableNames($tables)} ";
			}
		}

		$sql .= 'FROM '
		. $this->escapeTableName($this->query->getTable())
		. $this->joins($this->query->getJoins())
		. $this->wheres($this->query->getWheres());

		return ['sql' => $sql, 'params' => $this->params];
	}
}
