<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties;

/**
 * Value type enum.
 */
enum Type: int
{
	case Struct = 0x00000100;

	case Array = 0x00000200;

	case Qualifier = 0x00000020;
}
