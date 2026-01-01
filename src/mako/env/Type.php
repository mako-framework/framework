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
    case BOOL;
    case INT;
    case FLOAT;
    case JSON_AS_OBJECT;
    case JSON_AS_ARRAY;
}
