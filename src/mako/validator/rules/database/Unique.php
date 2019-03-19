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
 * Unique rule.
 *
 * @author Frederic G. Østby
 */
class Unique extends Rule implements RuleInterface
{
	/**
	 * Table.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * Column.
	 *
	 * @var string
	 */
	protected $column;

	/**
	 * Allowed value.
	 *
	 * @var mixed
	 */
	protected $allowed;

	/**
	 * Connection.
	 *
	 * @var string|null
	 */
	protected $connection;

	/**
	 * Connection manager.
	 *
	 * @var \mako\database\ConnectionManager
	 */
	protected $database;

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
	 * @param mixed                            $allowed    Allowed value
	 * @param string|null                      $connection Connection
	 * @param \mako\database\ConnectionManager $database   Connection manager
	 */
	public function __construct(string $table, string $column, $allowed = null, ?string $connection, ConnectionManager $database)
	{
		$this->table = $table;

		$this->column = $column;

		$this->allowed = $allowed;

		$this->connection = $connection;

		$this->database = $database;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		if($this->allowed !== null && $this->allowed === $value)
		{
			return true;
		}

		$count = $this->database->connection($this->connection)
		->table($this->table)
		->where($this->column, '=', $value);

		if($this->allowed !== null)
		{
			$count->where($this->column, '!=', $this->allowed);
		}

		return $count->count() === 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be unique.', $field);
	}
}
