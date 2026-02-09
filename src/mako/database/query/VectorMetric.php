<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Vector metrics.
 */
enum VectorMetric: string
{
	case COSINE = 'COSINE';
	case EUCLIDEAN = 'EUCLIDEAN';
}
