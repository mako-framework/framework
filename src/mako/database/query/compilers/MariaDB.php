<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\VectorMetric;
use Override;

use function is_array;
use function json_encode;

/**
 * Compiles MariaDB queries.
 */
class MariaDB extends MySQL
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function whereVectorDistance(array $where): string
	{
		$vector = is_array($where['vector']) ? json_encode($where['vector']) : $where['vector'];

		$function = match ($where['metric']) {
			VectorMetric::COSINE => 'VEC_DISTANCE_COSINE',
			VectorMetric::EUCLIDEAN => 'VEC_DISTANCE_EUCLIDEAN',
		};

		return "{$function}({$this->column($where['column'], false)}, VEC_FromText({$this->param($vector)})) <= {$this->param($where['distance'])}";
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
}
