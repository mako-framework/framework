<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Vector metrics.
 */
enum VectorMetric
{
	case COSINE;
	case EUCLIDEAN;
}
