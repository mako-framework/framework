<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

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
 *
 * @author Frederic G. Østby
 */
class Alert
{
	/**
	 * Alert padding.
	 *
	 * @var int
	 */
	public const PADDING = 1;

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
	 * Output instance.
	 *
	 * @var \mako\cli\output\Output
	 */
	protected $output;

	/**
	 * Alert width.
	 *
	 * @var int
	 */
	protected $width;

	/**
	 * Formatter.
	 *
	 * @var \mako\cli\output\formatter\FormatterInterface|null
	 */
	protected $formatter;

	/**
	 * Constructor.
	 *
	 * @param \mako\cli\output\Output $output Output instance
	 * @param int|null                $width  Alert width
	 */
	public function __construct(Output $output, ?int $width = null)
	{
		$this->output = $output;

		$this->width = $width ?? $output->getEnvironment()->getWidth();

		$this->formatter = $output->getFormatter();
	}

	/**
	 * Wraps a string to a given number of characters.
	 *
	 * @param  string $string String
	 * @param  int    $width  Max line width
	 * @return string
	 */
	protected function wordWrap(string $string, int $width): string
	{
		return trim(preg_replace(sprintf('/(.{1,%1$u})(?:\s|$)|(.{%1$u})/uS', $width), '$1$2' . PHP_EOL, $string));
	}

	/**
	 * Escapes style tags if we have a formatter.
	 *
	 * @param  string $string string
	 * @return string
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
	 *
	 * @param  string $string String
	 * @return string
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
	 *
	 * @param  string $message  Message
	 * @param  string $template Alert template
	 * @return string
	 */
	public function render(string $message, string $template = Alert::DEFAULT): string
	{
		return sprintf($template, $this->format($message)) . PHP_EOL;
	}

	/**
	 * Draws an alert.
	 *
	 * @param string $message  Message
	 * @param string $template Alert template
	 * @param int    $writer   Output writer
	 */
	public function draw(string $message, string $template = Alert::DEFAULT, int $writer = Output::STANDARD): void
	{
		$this->output->write($this->render($message, $template), $writer);
	}
}
