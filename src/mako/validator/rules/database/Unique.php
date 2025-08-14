<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\database;

use mako\database\ConnectionManager;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use Override;

use function sprintf;

/**
 * Unique rule.
 */
class Unique extends Rule implements RuleInterface
{
	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['table', 'column'];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $table,
		protected string $column,
		protected mixed $allowed,
		protected ?string $connection,
		protected ConnectionManager $database
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		if ($this->allowed !== null && $this->allowed === $value) {
			return true;
		}

		$count = $this->database->getConnection($this->connection)
		->getQuery()
		->table($this->table)
		->where($this->column, '=', $value);

		if ($this->allowed !== null) {
			$count->where($this->column, '!=', $this->allowed);
		}

		return $count->count() === 0;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be unique.', $field);
	}
}
