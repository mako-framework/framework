<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

use Override;
use PDO;

/**
 * Large object type.
 */
class LargeObject extends Type
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getType(): int
	{
		return PDO::PARAM_LOB;
	}
}
