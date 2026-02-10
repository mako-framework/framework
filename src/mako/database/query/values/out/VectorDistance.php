<?php

namespace mako\database\query\values\out;

use mako\database\exceptions\DatabaseException;
use mako\database\query\compilers\Compiler;
use mako\database\query\compilers\MariaDB;
use mako\database\query\compilers\MySQL;
use mako\database\query\compilers\Postgres;
use mako\database\query\VectorDistance as VectorDistanceType;
use Override;

use function is_array;
use function json_encode;
use function sprintf;

/**
 * Vector distance output value.
 */
class VectorDistance extends Value
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $column,
		protected array|string $vector,
		protected VectorDistanceType $vectorDistance = VectorDistanceType::COSINE,
		protected ?string $alias = null
	) {
	}

	/**
	 * Gets the MariaDB distance SQL.
	 */
	protected function getMariaDbDistance(Compiler $compiler): string
	{
		$function = match ($this->vectorDistance) {
			VectorDistanceType::COSINE => 'VEC_DISTANCE_COSINE',
			VectorDistanceType::EUCLIDEAN => 'VEC_DISTANCE_EUCLIDEAN',
		};

		return "{$function}({$compiler->escapeColumnName($this->column)}, VEC_FromText(?))";
	}

	/**
	 * Gets the MySQL distance SQL.
	 */
	protected function getMySqlDistance(Compiler $compiler): string
	{
		$function = match ($this->vectorDistance) {
			VectorDistanceType::COSINE => 'COSINE',
			VectorDistanceType::EUCLIDEAN => 'EUCLIDEAN',
		};

		return "DISTANCE({$compiler->escapeColumnName($this->column)}, STRING_TO_VECTOR(?), '{$function}')";
	}

	/**
	 * Gets the Postgres distance SQL.
	 */
	protected function getPostgresDistance(Compiler $compiler): string
	{
		$function = match ($this->vectorDistance) {
			VectorDistanceType::COSINE => '<=>',
			VectorDistanceType::EUCLIDEAN => '<->',
		};

		return "{$compiler->columnName($this->column)} {$function} ?";
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getSql(Compiler $compiler): string
	{
		return match ($compiler::class) {
			MariaDB::class => $this->getMariaDbDistance($compiler),
			MySQL::class => $this->getMySqlDistance($compiler),
			Postgres::class => $this->getPostgresDistance($compiler),
			default => throw new DatabaseException(sprintf('Vector values are not supported by the [ %s ] compiler.', $compiler::class)),
		};
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getParameters(): ?array
	{
		return [
			is_array($this->vector) ? json_encode($this->vector) : $this->vector,
		];
	}
}
