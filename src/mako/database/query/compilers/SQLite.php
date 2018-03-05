<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\traits\JsonPathBuilderTrait;

/**
 * Compiles SQLite queries.
 *
 * @author Frederic G. Østby
 * @author Yamada Taro
 */
class SQLite extends Compiler
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
	protected function buildJsonGet(string $column, array $segments): string
	{
		return 'JSON_EXTRACT(' . $column . ", '" . $this->buildJsonPath($segments) . "')";
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . ' = JSON_SET(' . $column . ", '" . $this->buildJsonPath($segments) . "', JSON(" . $param . '))';
	}
}
