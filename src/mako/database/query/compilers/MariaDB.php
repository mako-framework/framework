<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\Subquery;
use mako\database\query\VectorDistance;
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
		$vector = $where['vector'];

		if ($vector instanceof Subquery) {
			$vector = $this->subquery($vector);
		}
		else {
			if (is_array($vector)) {
				$vector = json_encode($vector);
			}

			$vector = "VEC_FromText({$this->param($vector)})";
		}

		$function = match ($where['vectorDistance']) {
			VectorDistance::COSINE => 'VEC_DISTANCE_COSINE',
			VectorDistance::EUCLIDEAN => 'VEC_DISTANCE_EUCLIDEAN',
		};

		return "{$function}({$this->column($where['column'], false)}, {$vector}) <= {$this->param($where['maxDistance'])}";
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
