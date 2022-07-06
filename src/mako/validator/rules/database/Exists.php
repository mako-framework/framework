<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\database;

use mako\database\ConnectionManager;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

use function sprintf;

/**
 * Exists rule.
 */
class Exists extends Rule implements RuleInterface
{
	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['table', 'column'];

	/**
	 * Constructor.
	 *
	 * @param string                           $table      Table
	 * @param string                           $column     Column
	 * @param string|null                      $connection Connection
	 * @param \mako\database\ConnectionManager $database   Connection manager
	 */
	public function __construct(
		protected string $table,
		protected string $column,
		protected ?string $connection,
		protected ConnectionManager $database
	)
	{}

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $this->database->getConnection($this->connection)
		->getQuery()
		->table($this->table)
		->where($this->column, '=', $value)
		->count() !== 0;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s doesn\'t exist.', $field);
	}
}
