<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;
use mako\database\query\compilers\traits\JsonPathBuilderTrait;

/**
 * Compiles MySQL queries.
 *
 * @author Frederic G. Østby
 */
class MySQL extends Compiler
{
	use JsonPathBuilderTrait;

	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected static $dateFormat = 'Y-m-d H:i:s';

	/**
	 * {@inheritdoc}
	 */
	public function escapeIdentifier(string $identifier): string
	{
		return '`' . str_replace('`', '``', $identifier) . '`';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonGet(string $column, array $segments): string
	{
		return $column . "->>'" . $this->buildJsonPath($segments) . "'";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . ' = JSON_SET(' . $column . ", '" . $this->buildJsonPath($segments) . "', CAST(" . $param . ' AS JSON))';
	}

	/**
	 * {@inheritdoc}
	 */
	public function lock($lock): string
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' LOCK IN SHARE MODE' : ' ' . $lock);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function insertWithoutValues(): string
	{
		return 'INSERT INTO ' . $this->escapeTable($this->query->getTable()) . ' () VALUES ()';
	}
}
