<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\VectorMetric;
use Override;

use function array_pop;
use function implode;
use function is_array;
use function is_numeric;
use function json_encode;
use function str_replace;

/**
 * Compiles Postgres queries.
 */
class Postgres extends Compiler
{
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
		$sql = [];

		foreach ($segments as $segment) {
			$sql[] = is_numeric($segment) ? $segment : "'" . str_replace("'", "''", $segment) . "'";
		}

		$last = array_pop($sql);

		if (empty($sql)) {
			return "{$column}->>{$last}";
		}

		return "{$column}->" . implode('->', $sql) . "->>{$last}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . " = JSONB_SET({$column}, '{" . str_replace("'", "''", implode(',', $segments)) . "}', '{$param}')";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function whereVectorDistance(array $where): string
	{
		$vector = is_array($where['vector']) ? json_encode($where['vector']) : $where['vector'];

		$operator = match ($where['metric']) {
			VectorMetric::COSINE => '<=>',
			VectorMetric::EUCLIDEAN => '<->',
		};

		return "{$this->column($where['column'], false)} {$operator} {$this->param($vector)} <= {$this->param($where['distance'])}";
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
				return "{$this->columnName($where['column'])}::date::char(10) {$where['operator']} {$this->simpleParam($where['value'])}";
		}
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

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR SHARE' : " {$lock}");
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
