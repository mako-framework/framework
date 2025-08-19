<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp\properties\traits;

use function preg_replace;
use function strrchr;
use function substr;

/**
 * Short name trait.
 */
trait ShortNameTrait
{
	public string $shortName {
		get {
			return preg_replace('/\[\d+\](?!.*\[\d+\])/', '', substr(strrchr($this->name, ':'), 1));
		}
	}
}
