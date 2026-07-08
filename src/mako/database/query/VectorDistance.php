<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Vector distance.
 */
enum VectorDistance: string
{
	case Cosine = 'cosine';
	case Euclidean = 'euclidean';
	case Manhattan = 'manhattan';
    case InnerProduct = 'inner_product';
}
