<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\midgard\relations\traits\HasOneOrManyPolymorphicTrait;

/**
 * Has one polymorphic relation.
 *
 * @author Frederic G. Østby
 */
class HasOnePolymorphic extends HasOne
{
	use HasOneOrManyPolymorphicTrait;
}
