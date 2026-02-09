<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Vector distance.
 */
enum VectorDistance
{
	case COSINE;
	case EUCLIDEAN;
}
