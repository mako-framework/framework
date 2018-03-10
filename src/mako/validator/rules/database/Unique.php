<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\database;

use mako\database\ConnectionManager;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\WithParametersTrait;
use mako\validator\rules\WithParametersInterface;

/**
 * Unique rule.
 *
 * @author Frederic G. Østby
 */
class Unique extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['table', 'column', 'allowed', 'connection'];

	/**
	 * Connection manager.
	 *
	 * @var \mako\database\ConnectionManager
	 */
	protected $database;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\ConnectionManager $database Connection manager
	 */
	public function __construct(ConnectionManager $database)
	{
		$this->database = $database;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		if(($allowed = $this->getParameter('allowed', true)) !== null && $allowed === $value)
		{
			return true;
		}

		$count = $this->database->connection($this->getParameter('connection', true))
		->table($this->getParameter('table'))
		->where($this->getParameter('column'), '=', $value)
		->count();

		return $count === 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be unique.', $field);
	}
}
