<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\components\hyperlink\Theme;
use mako\cli\output\Output;

use function hash;
use function in_array;
use function mako\env;
use function sprintf;

/**
 * Hyperlink component.
 */
class Hyperlink
{
	/**
	 * Unsupported terminals.
	 */
	protected const array UNSUPPORTED_TERMINALS = [
		'Apple_Terminal',
	];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		protected Theme $theme = new Theme
	) {
	}

	/**
	 * Returns TRUE if the terminal supports hyperlinks and FALSE if not.
	 */
	protected function hasHyperlinkSupport(): bool
	{
		return $this->output->environment->hasAnsiSupport() && !in_array(env('TERM_PROGRAM'), static::UNSUPPORTED_TERMINALS);
	}

	/**
	 * Renders a hyperlink.
	 */
	public function render(string $url, ?string $text = null): string
	{
		if ($this->hasHyperlinkSupport()) {
			return sprintf(
				"\x1b]8;id=%s;%s\x1b\\%s\x1b]8;;\x1b\\",
				hash('xxh128', $url),
				$url,
				sprintf($this->theme->getFormat(), $text ?? $url)
			);
		}

		return $text ? "{$text} ({$url})" : $url;
	}

	/**
	 * Draws a hyperlink.
	 */
	public function draw(string $url, ?string $text = null, int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($url, $text), $writer);
	}
}
