<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\chrono;

use DateTime;
use mako\chrono\traits\TimeTrait;

/**
 * Extension of the PHP DateTime class.
 *
 * @author Frederic G. Østby
 *
 * @method $this|false setTimezone(string|\DateTimeZone $timeZone)
 * @method $this|false forward(int seconds)
 * @method $this|false rewind(int seconds)
 */
class Time extends DateTime implements TimeInterface
{
	use TimeTrait;
}
