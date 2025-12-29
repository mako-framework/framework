<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\Output;

use function sprintf;
use function str_replace;

/**
 * Notification component.
 */
class Notification
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output
	) {
	}

	/**
	 * Normalizes string for notifications.
	 */
	protected function normalizeString(string $string): string
	{
		return str_replace(';', ':', $string);
	}

	/**
	 * Sends a notification.
	 */
	public function notify(string $title, string $body): void
	{
		if (!$this->output->environment->hasAnsiSupport()) {
			return;
		}

		$this->output->write(sprintf(
			"\033]777;notify;%s;%s\x07",
			$this->normalizeString($title),
			$this->normalizeString($body)
		));
	}
}
