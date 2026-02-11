<?php

namespace mako\database\query\values\in;

use mako\database\exceptions\DatabaseException;
use mako\database\query\compilers\Compiler;
use mako\database\query\compilers\MariaDB;
use mako\database\query\compilers\MySQL;
use mako\database\query\compilers\Postgres;
use mako\database\query\values\ValueInterface;
use Override;

use function is_array;
use function json_encode;
use function sprintf;

/**
 * Vector input value.
 */
class Vector implements ValueInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected array|string $vector
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getSql(Compiler $compiler): string
	{
		return match ($compiler::class) {
			MariaDB::class => 'VEC_FromText(?)',
			MySQL::class => 'STRING_TO_VECTOR(?)',
			Postgres::class => '?',
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
