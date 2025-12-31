<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use DateTimeInterface;
use mako\i18n\I18n;

use function abs;
use function date;
use function floor;
use function log;
use function round;
use function time;

/**
 * Makes data more human friendly.
 */
class Humanizer
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected ?I18n $i18n = null
	) {
	}

	/**
	 * Returns a human friendly file size.
	 */
	public function fileSize(float|int $size, bool $binary = true): string
	{
		if ($size > 0) {
			if ($binary === true) {
				$base  = 1024;
				$terms = ['byte', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
			}
			else {
				$base  = 1000;
				$terms = ['byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
			}

			$e = floor(log($size, $base));

			return round($size / ($base ** $e), 2) . " {$terms[(int) $e]}";
		}

		return '0 byte';
	}

	/**
	 * Returns a human friendly representation of the date.
	 */
	public function day(DateTimeInterface $dateTime, string $dateFormat = 'Y-m-d, H:i'): string
	{
		if ($dateTime->format('Y-m-d') === date('Y-m-d')) {
			return $this->i18n->get('humanizer.today');
		}
		elseif ($dateTime->format('Y-m-d') === date('Y-m-d', time() - (60 * 60 * 24))) {
			return $this->i18n->get('humanizer.yesterday');
		}
		elseif ($dateTime->format('Y-m-d') === date('Y-m-d', time() + (60 * 60 * 24))) {
			return $this->i18n->get('humanizer.tomorrow');
		}
		else {
			return $dateTime->format($dateFormat);
		}
	}

	/**
	 * Returns a human friendly representation of the time.
	 */
	public function time(DateTimeInterface $dateTime, string $dateFormat = 'Y-m-d, H:i', string $clockFormat = ', H:i'): string
	{
		$diff = time() - $dateTime->getTimestamp();

		if ($diff < 0) {
			// Our date is in the future

			$diff = abs($diff);

			if ($diff < 120) {
				return $this->i18n->get('humanizer.in_minute');
			}
			elseif ($diff < 3600) {
				return $this->i18n->get('humanizer.in_minutes', [$diff / 60]);
			}
			elseif ($dateTime->format('Y-m-d') === date('Y-m-d')) {
				return $this->i18n->get('humanizer.today') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
			elseif ($dateTime->format('Y-m-d') === date('Y-m-d', time() + (60 * 60 * 24))) {
				return $this->i18n->get('humanizer.tomorrow') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
		}
		else {
			// Our date is in the past

			if ($diff < 120) {
				return $this->i18n->get('humanizer.minute_ago');
			}
			elseif ($diff < 3600) {
				return $this->i18n->get('humanizer.minutes_ago', [$diff / 60]);
			}
			elseif ($dateTime->format('Y-m-d') === date('Y-m-d')) {
				return $this->i18n->get('humanizer.today') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
			elseif ($dateTime->format('Y-m-d') === date('Y-m-d', time() - (60 * 60 * 24))) {
				return $this->i18n->get('humanizer.yesterday') . (!empty($clockFormat) ? $dateTime->format($clockFormat) : '');
			}
		}

		// None of the above so just return the default date format

		return $dateTime->format($dateFormat);
	}
}
