<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\midgard\relations\HasMany;
use mako\database\midgard\relations\traits\HasOneOrManyPolymorphicTrait;

/**
 * Has many polymorphic relation.
 *
 * @author Frederic G. Østby
 */
class HasManyPolymorphic extends HasMany
{
	use HasOneOrManyPolymorphicTrait;
}
