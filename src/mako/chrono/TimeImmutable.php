<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTimeImmutable;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTimeImmutable class.
 *
 * @author Frederic G. Østby
 */
class TimeImmutable extends DateTimeImmutable implements TimeInterface
{
	use TimeTrait;
}
