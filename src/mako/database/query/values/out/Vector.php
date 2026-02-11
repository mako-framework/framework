<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\values\out;

use mako\database\exceptions\DatabaseException;
use mako\database\query\compilers\Compiler;
use mako\database\query\compilers\MariaDB;
use mako\database\query\compilers\MySQL;
use mako\database\query\compilers\Postgres;
use Override;

use function sprintf;

/**
 * Vector output value.
 */
class Vector extends Value
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $column,
		protected ?string $alias = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getSql(Compiler $compiler): string
	{
		return match ($compiler::class) {
			MariaDB::class => "VEC_ToText({$compiler->escapeColumnName($this->column)})",
			MySQL::class => "VECTOR_TO_STRING({$compiler->escapeColumnName($this->column)})",
			Postgres::class => "{$compiler->escapeColumnName($this->column)}",
			default => throw new DatabaseException(sprintf('Vector values are not supported by the [ %s ] compiler.', $compiler::class)),
		};
	}
}
