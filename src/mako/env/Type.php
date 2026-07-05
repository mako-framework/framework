<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\env;

/**
 * Type.
 */
enum Type
{
	/**
	 * Cast the value to a boolean.
	 */
	case Bool;

	/**
	 * Cast the value to an integer.
	 */
	case Int;

	/**
	 * Cast the value to a float.
	 */
	case Float;

	/**
	 * Cast the value to an object.
	 */
	case JsonAsObject;

	/**
	 * Cast the value to an array.
	 */
	case JsonAsArray;
}
