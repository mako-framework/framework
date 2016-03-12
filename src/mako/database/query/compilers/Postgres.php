<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\query\compilers;

use mako\database\query\compilers\Compiler;

/**
 * Compiles Postgres queries.
 *
 * @author  Frederic G. Østby
 * @author  Yamada Taro
 */
class Postgres extends Compiler
{
	/**
	 * {@inheritdoc}
	 */
	public function lock($lock)
	{
		if($lock === null)
		{
			return '';
		}

		return $lock === true ? ' FOR UPDATE' : ($lock === false ? ' FOR SHARE' : ' ' . $lock);
	}
}