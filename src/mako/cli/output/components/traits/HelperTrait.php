<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\traits;

use function array_map;
use function implode;
use function mb_strwidth;
use function preg_replace;
use function preg_split;
use function trim;

/**
 * Helper trait.
 *
 * @property \mako\cli\output\Output $output
 */
trait HelperTrait
{
	/**
	 * Returns the visible width of the string without formatting.
	 */
	protected function getVisibleStringWidth(string $string): int
	{
		// Strip tags if a formatter is set

		$string = $this->output->formatter !== null ? $this->output->formatter->stripTags($string) : $string;

		// Strip ANSI codes and OSC sequences

		$string =  preg_replace('/\033\[[0-?9;]*[mK]|(\033\]8;.*?\033\\\)/', '', $string);

		// Return the width of the string

		return (int) mb_strwidth($string);
	}

	/**
	 * Wraps a string to a given number of characters.
	 */
	protected function wordWrap(string $string, int $width, bool $returnAsArray = false): array|string
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
				// Try to break at a space in the last 8 characters
				$breakPos = mb_strrpos(mb_substr($line, -8), ' ');

				if ($breakPos !== false) {
					$splitPos = mb_strlen($line) - (8 - $breakPos);
					$lines[] = mb_substr($line, 0, $splitPos);
					$line = mb_substr($line, $splitPos);
				}
				else {
					$lines[] = $line;
					$line = '';
				}
			}
		}

		if ($line !== '') {
			$lines[] = $line;
		}

		$lines = array_map(trim(...), $lines);

		if ($returnAsArray) {
			return $lines;
		}

		return implode(PHP_EOL, $lines);
	}
}
