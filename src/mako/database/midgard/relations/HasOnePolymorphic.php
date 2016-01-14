<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;
use mako\database\midgard\relations\HasOne;
use mako\database\midgard\relations\HasOneOrManyPolymorphicTrait;

/**
 * Has one polymorphic relation.
 *
 * @author  Frederic G. Østby
 */

class HasOnePolymorphic extends HasOne
{
	use HasOneOrManyPolymorphicTrait;
}