<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\env;

use Deprecated;

/**
 * Type.
 */
enum Type
{
	/* Start compatibility */
	#[Deprecated('use Type::Bool instead', 'Mako 12.2.0')]
	public const BOOL = self::Bool;
	#[Deprecated('use Type::Int instead', 'Mako 12.2.0')]
    public const INT = self::Int;
	#[Deprecated('use Type::Float instead', 'Mako 12.2.0')]
    public const FLOAT = self::Float;
	#[Deprecated('use Type::JsonAsObject instead', 'Mako 12.2.0')]
    public const JSON_AS_OBJECT = self::JsonAsObject;
	#[Deprecated('use Type::JsonAsArray instead', 'Mako 12.2.0')]
    public const JSON_AS_ARRAY = self::JsonAsArray;
	/* End compatibility */

    case Bool;
    case Int;
    case Float;
    case JsonAsObject;
    case JsonAsArray;
}
