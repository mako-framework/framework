<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles Firebird queries.
 *
 * @author  Frederic G. Østby
 */
class Firebird extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	protected function limit($limit)
	{
		$offset = $this->query->getOffset();

		return ($offset === null) ? ($limit === null) ? '' :' ROWS 1 ' : ' ROWS ' . ($offset + 1);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function offset($offset)
	{
		$limit = $this->query->getLimit();

		return ($limit === null) ? '' : ' TO ' . ($limit + (($offset === null) ? 0 : $offset));
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

		return $lock === true ? ' FOR UPDATE WITH LOCK' : ($lock === false ? ' WITH LOCK' : ' ' . $lock);
	}
}