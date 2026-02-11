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
	 * Returns a vector distance calculation.
	 */
	protected function vectorDistance(array $vectorDistance): string
	{
		$vector = $vectorDistance['vector'];

		if ($vector instanceof Subquery) {
			$vector = $this->subquery($vector);
		}
		else {
			if (is_array($vector)) {
				$vector = json_encode($vector);
			}

			$vector = "VEC_FromText({$this->param($vector)})";
		}

		$function = match ($vectorDistance['vectorDistance']) {
			VectorDistance::COSINE => 'VEC_DISTANCE_COSINE',
			VectorDistance::EUCLIDEAN => 'VEC_DISTANCE_EUCLIDEAN',
		};

		return "{$function}({$this->column($vectorDistance['column'], false)}, {$vector})";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function whereVectorDistance(array $where): string
	{
		return "{$this->vectorDistance($where)} <= {$this->param($where['maxDistance'])}";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function vectorDistanceOrdering(array $order): string
	{
		return "{$this->vectorDistance($order)} {$order['order']}";
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
