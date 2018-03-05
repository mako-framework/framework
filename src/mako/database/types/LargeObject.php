<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

use PDO;

/**
 * Large object type.
 *
 * @author Frederic G. Østby
 */
class LargeObject extends Type
{
	/**
	 * PDO parameter type.
	 *
	 * @var int
	 */
	const TYPE = PDO::PARAM_LOB;
}
