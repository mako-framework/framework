<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components;

use mako\cli\output\components\traits\HelperTrait;
use mako\cli\output\formatter\FormatterInterface;
use mako\cli\output\Output;

use function explode;
use function implode;
use function max;
use function sprintf;
use function str_repeat;

/**
 * Alert component.
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
