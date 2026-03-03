<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

use Deprecated;

/**
 * Value type enum.
 */
enum Type: int
{
	/* Start compatibility */
	#[Deprecated('use Type::Struct instead', 'Mako 12.2.0')]
	public const STRUCT = self::Struct;
	#[Deprecated('use Type::Array instead', 'Mako 12.2.0')]
	public const ARRAY = self::Array;
	#[Deprecated('use Type::Qualifier instead', 'Mako 12.2.0')]
	public const QUALIFIER = self::Qualifier;
	/* End compatibility */

	case Struct = 0x00000100;

	case Array = 0x00000200;

	case Qualifier = 0x00000020;
}
