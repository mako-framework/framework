<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\types;

use PDO;

use mako\database\types\Type;

/**
 * Large object type.
 *
 * @author  Frederic G. Østby
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