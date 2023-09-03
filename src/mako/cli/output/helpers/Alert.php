<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\Output;

use function explode;
use function implode;
use function mb_strlen;
use function preg_replace;
use function sprintf;
use function str_repeat;
use function trim;

/**
 * Alert helper.
 */
class Alert
{
	/**
	 * Alert padding.
	 *
	 * @var int
	 */
	protected const PADDING = 1;

	/**
	 * Default template.
	 *
	 * @var string
	 */
	public const DEFAULT = '%s';

	/**
	 * Info template.
	 *
	 * @var string
	 */
	public const INFO = '<bg_blue><black>%s</black></bg_blue>';

	/**
	 * Success template.
	 *
	 * @var string
	 */
	public const SUCCESS = '<bg_green><black>%s</black></bg_green>';

	/**
	 * Warning template.
	 *
	 * @var string
	 */
	public const WARNING = '<bg_yellow><black>%s</black></bg_yellow>';

	/**
	 * Danger template.
	 *
	 * @var string
	 */
	public const DANGER = '<bg_red><white>%s</white></bg_red>';

	/**
	 * Alert width.
	 */
	protected int $width;

	/**
	 * Formatter.
	 */
	protected null|FormatterInterface $formatter = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected Output $output,
		?int $width = null
	)
	{
		$this->width = $width ?? $output->getEnvironment()->getWidth();

		$this->formatter = $output->getFormatter();
	}

	/**
	 * Wraps a string to a given number of characters.
	 */
	protected function wordWrap(string $string, int $width): string
	{
		return trim(preg_replace(sprintf('/(.{1,%1$u})(?:\s|$)|(.{%1$u})/uS', $width), '$1$2' . PHP_EOL, $string));
	}

	/**
	 * Escapes style tags if we have a formatter.
	 */
	protected function escape(string $string): string
	{
		if($this->formatter !== null)
		{
			return $this->formatter->escape($string);
		}

		return $string;
	}

	/**
	 * Formats the string.
	 */
	protected function format(string $string): string
	{
		$lineWidth = $this->width - (static::PADDING * 2);

		$lines = explode(PHP_EOL, PHP_EOL . $this->wordWrap($string, $lineWidth) . PHP_EOL);

		foreach($lines as $key => $value)
		{
			$value = $this->escape($value) . str_repeat(' ', $lineWidth - mb_strlen($value));

			$lines[$key] = sprintf('%1$s%2$s%1$s', str_repeat(' ', static::PADDING), $value);
		}

		return implode(PHP_EOL, $lines);
	}

	/**
	 * Renders an alert.
	 */
	public function render(string $message, string $template = Alert::DEFAULT): string
	{
		return sprintf($template, $this->format($message)) . PHP_EOL;
	}

	/**
	 * Draws an alert.
	 */
	public function draw(string $message, string $template = Alert::DEFAULT, int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($message, $template), $writer);
	}
}
