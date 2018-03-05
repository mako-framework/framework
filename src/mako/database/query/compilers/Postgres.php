<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

/**
 * Compiles Postgres queries.
 *
 * @author Frederic G. Østby
 * @author Yamada Taro
 */
class Postgres extends Compiler
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
	protected function buildJsonGet(string $column, array $segments): string
	{
		$pieces = [];

		foreach($segments as $segment)
		{
			$pieces[] = is_numeric($segment) ? $segment : "'" . str_replace("'", "''", $segment) . "'";
		}

		$last = array_pop($pieces);

		if(empty($pieces))
		{
			return $column . '->>' . $last;
		}

		return $column . '->' . implode('->', $pieces) . '->>' . $last;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function buildJsonSet(string $column, array $segments, string $param): string
	{
		return $column . " = JSONB_SET(" . $column . ", '{" . str_replace("'", "''", implode(',', $segments)) . "}', '" . $param . "')";
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

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR SHARE' : ' ' . $lock);
	}
}
