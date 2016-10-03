<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles SQL Server queries.
 *
 * @author  Frederic G. Østby
 */
class SQLServer extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected static $dateFormat = 'Y-m-d H:i:s.0000000';

	/**
	 * {@inheritdoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '[' . str_replace(']', ']]', $identifier) . ']';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonPath(string $column, array $segments): string
	{
		$path = '';

		foreach($segments as $segment)
		{
			if(is_numeric($segment))
			{
				$path .= '[' . $segment . ']';
			}
			else
			{
				$path .= '.' . $segment;
			}
		}

		return 'json_value(' . $column . ', ' . "'lax $" . str_replace("'", "''", $path) . "'" . ')';
	}

	/**
	 * {@inheritdoc}
	 */
	public function from($from): string
	{
		$from = parent::from($from);

		if(($lock = $this->query->getLock()) !== null)
		{
			$from .= $lock === true ? ' WITH (UPDLOCK, ROWLOCK)' : ($lock === false ? ' WITH (HOLDLOCK, ROWLOCK)' : ' ' . $lock);
		}

		return $from;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function orderings(array $orderings): string
	{
		if(empty($orderings) && ($this->query->getLimit() !== null || $this->query->getOffset() !== null))
		{
			return ' ORDER BY (SELECT 0)';
		}

		return parent::orderings($orderings);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function limit(int $limit = null): string
	{
		$offset = $this->query->getOffset();

		if($limit === null)
		{
			return '';
		}

		return ' OFFSET ' . ($offset ?: 0) . ' ROWS FETCH NEXT ' . $limit . ' ROWS ONLY';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset(int $offset = null): string
	{
		$limit = $this->query->getLimit();

		if($limit === null && $offset !== null)
		{
			return ' OFFSET ' . $offset . ' ROWS';
		}

		return '';
	}
}