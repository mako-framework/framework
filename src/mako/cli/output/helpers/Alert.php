<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\helpers;

use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\helpers\traits\HelperTrait;
use mako\cli\output\Output;

use function array_map;
use function explode;
use function implode;
use function max;
use function preg_split;
use function sprintf;
use function str_repeat;

/**
 * Alert helper.
 */
class Alert
{
	use HelperTrait;

	/**
	 * Alert padding.
	 */
	protected const int PADDING = 1;

	/**
	 * Default template.
	 */
	public const string DEFAULT = '%s';

	/**
	 * Info template.
	 */
	public const string INFO = '<bg_blue><black>%s</black></bg_blue>';

	/**
	 * Success template.
	 */
	public const string SUCCESS = '<bg_green><black>%s</black></bg_green>';

	/**
	 * Warning template.
	 */
	public const string WARNING = '<bg_yellow><black>%s</black></bg_yellow>';

	/**
	 * Danger template.
	 */
	public const string DANGER = '<bg_red><white>%s</white></bg_red>';

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
	) {
		$this->width = $width ?? $output->getEnvironment()->getWidth();

		$this->formatter = $output->getFormatter();
	}

	/**
	 * Wraps a string to a given number of characters.
	 */
	protected function wordWrap(string $string, int $width): string
	{
		$characters = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

		$lines = [];
		$line = '';

		foreach ($characters as $character) {
			if ($character === PHP_EOL) {
				$lines[] = $line;
				$line = '';
				continue;
			}

			$line .= $character;

			if ($this->getVisibleStringWidth($line) >= $width - 1) {
				$lines[] = $line;
				$line = '';
			}
		}

		if ($line !== '') {
			$lines[] = $line;
		}

		return implode(PHP_EOL, array_map('trim', $lines));
	}

	/**
	 * Formats the string.
	 */
	protected function format(string $string): string
	{
		$lineWidth = $this->width - (static::PADDING * 2);

		$lines = explode(PHP_EOL, PHP_EOL . $this->wordWrap($string, $lineWidth) . PHP_EOL);

		foreach ($lines as $key => $value) {
			$value = $value . str_repeat(' ', max(0, $lineWidth - $this->getVisibleStringWidth($value)));

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
