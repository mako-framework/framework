<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders\stream\event;

use JsonSerializable;
use Stringable;

/**
 * Event stream field.
 */
class Field
{
	public function __construct(
		public protected(set) Type $type,
		public protected(set) null|float|int|JsonSerializable|string|Stringable $value
	) {
	}
}
