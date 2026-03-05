<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\query;

/**
 * Sort direction.
 */
enum SortDirection: string
{
	case Ascending = 'ASC';
	case Descending = 'DESC';
}
