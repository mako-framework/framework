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
	case STRUCT = 0x00000100;

	case ARRAY = 0x00000200;

	case QUALIFIER = 0x00000020;
}