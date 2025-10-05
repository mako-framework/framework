<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\components\traits;

use function implode;
use function ltrim;
use function mb_strrpos;
use function mb_strwidth;
use function mb_substr;
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
				$lines[] = trim($line);
				$line = '';

				continue;
			}

			$lineWidth = $this->getVisibleStringWidth($line);
			$nextCharacterWidth = $this->getVisibleStringWidth($character);

			if ($lineWidth + $nextCharacterWidth <= $width) {
				$line .= $character;
			}
			else {
				if ($character !== ' ' && ($lastSpacePos = mb_strrpos($line, ' ')) !== false) {
					// If there is a space within the last 15 characters then we'll break on that
					// and put the rest of the string on a new line

					if ($this->getVisibleStringWidth(mb_substr($line, 0, $lastSpacePos)) >= $width - 15) {
						$lines[] = trim(mb_substr($line, 0, $lastSpacePos));
						$line = ltrim(mb_substr($line, $lastSpacePos + 1)) . $character;

						continue;
					}
				}

				$lines[] = $line;
				$line = trim($character);
			}
		}

		if ($line !== '') {
			$lines[] = trim($line);
		}

		if ($returnAsArray) {
			return $lines;
		}

		return implode(PHP_EOL, $lines);
	}
}
