<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

use Deprecated;

/**
 * Vector distance.
 */
enum VectorDistance
{
	/* Start compatibility */
	#[Deprecated('use VectorDistance::Cosine instead', 'Mako 12.2.0')]
	public const COSINE = self::Cosine;
	#[Deprecated('use VectorDistance::Euclidean instead', 'Mako 12.2.0')]
	public const EUCLIDEAN = self::Euclidean;
	/* End compatibility */

	case Cosine;
	case Euclidean;
}
