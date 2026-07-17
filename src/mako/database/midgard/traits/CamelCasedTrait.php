<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\midgard\ORM;

/**
 * Camel cased trait.
 *
 * @phpstan-require-extends ORM
 */
trait CamelCasedTrait
{
	use CamelCasedDataExportTrait;
	use CamelCasedDataInteractionTrait;
}
