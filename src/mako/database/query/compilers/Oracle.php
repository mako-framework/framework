<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles Oracle queries.
 *
 * @author  Frederic G. Østby
 */
class Oracle extends Compiler
{
	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonPath($column, array $segments)
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
				$path .= '.' . $this->escapeIdentifier($segment);
			}
		}

		return $column . $path;
	}

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock)
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR UPDATE' : ' ' . $lock);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function orderings(array $orderings)
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
	protected function limit($limit)
	{
		$offset = $this->query->getOffset();

		if($limit === null)
		{
			return '';
		}

		if($offset === null)
		{
			return ' FETCH FIRST ' . $limit . ' ROWS ONLY';
		}

		return ' OFFSET ' . $offset . ' ROWS FETCH NEXT ' . $limit . ' ROWS ONLY';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset($offset)
	{
		$limit = $this->query->getLimit();

		if($limit === null && $offset !== null)
		{
			return ' OFFSET ' . $offset . ' ROWS';
		}

		return '';
	}
}