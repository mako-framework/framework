<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\formatter;

use mako\cli\output\formatter\exceptions\FormatterException;
use Override;

use function array_last;
use function array_pop;
use function implode;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;

/**
 * Formatter.
 */
class Formatter implements FormatterInterface
{
	/**
	 * Regex that matches non-escaped tags.
	 */
	protected const string TAG_REGEX = '/(?<!\\\\)<\/?[a-z_]+\>/i';

	/**
	 * Regex that matches escaped tags.
	 */
	protected const string ESCAPED_TAG_REGEX = '/\\\\<(\/?[a-z_]+)\>/i';

	/**
	 * Regex that mathes ANSI SGR sequences.
	 */
	protected const string ANSI_SGR_SEQUENCE_REGEX = "/\x1b\[([0-9]{1,2};?)+m/";

	/**
	 * Styles.
	 */
	protected array $styles = [
		// Clear styles

		'clear'      => 0,

		// Text options

		'bold'       => 1,
		'faded'      => 2,
		'underlined' => 4,
		'blinking'   => 5,
		'reversed'   => 7,
		'hidden'     => 8,

		// Foreground colors

		'black'      => 30,
		'red'        => 31,
		'green'      => 32,
		'yellow'     => 33,
		'blue'       => 34,
		'purple'     => 35,
		'cyan'       => 36,
		'white'      => 37,

		// Background colors

		'bg_black'   => 40,
		'bg_red'     => 41,
		'bg_green'   => 42,
		'bg_yellow'  => 43,
		'bg_blue'    => 44,
		'bg_purple'  => 45,
		'bg_cyan'    => 46,
		'bg_white'   => 47,
	];

	/**
	 * User styles.
	 */
	protected array $userStyles = [];

	/**
	 * Open tags.
	 */
	protected array $openTags = [];

	/**
	 * Adds a user defined style.
	 */
	public function addStyle(string $name, array|string $style): void
	{
		$this->userStyles[$name] = (array) $style;
	}

	/**
	 * Returns the tag name.
	 */
	protected function getTagName(string $tag): string
	{
		return str_replace(['<', '>', '/'], '', $tag);
	}

	/**
	 * Returns TRUE if the tag is a closing tag and FALSE if not.
	 */
	protected function isOpeningTag(string $tag): bool
	{
		return str_starts_with($tag, '</') === false;
	}

	/**
	 * Returns ANSI SGR escape sequence for style reset.
	 */
	protected function getSgrResetSequence(): string
	{
		return "\x1b[0m";
	}

	/**
	 * Returns style codes associated with the tag name.
	 */
	protected function getStyleCodes(string $tag): array
	{
		if (isset($this->styles[$tag])) {
			return [$this->styles[$tag]];
		}
		elseif (isset($this->userStyles[$tag])) {
			$codes = [];

			foreach ($this->userStyles[$tag] as $tag) {
				$codes = [...$codes, ...$this->getStyleCodes($tag)];
			}

			return $codes;
		}

		throw new FormatterException(sprintf('Undefined formatting tag [ %s ] detected.', $tag));
	}

	/**
	 * Returns ANSI SGR escape sequence for the chosen style(s).
	 */
	protected function getSgrStyleSequence(string $tag): string
	{
		$styles = implode(';', $this->getStyleCodes($tag));

		return sprintf("\x1b[%sm", $styles);
	}

	/**
	 * Returns ANSI SGR escape sequence(s) for the chosen style(s) and
	 * adds the tag name to the array of open tags.
	 */
	protected function openStyle(string $tag): string
	{
		$this->openTags[] = $tagName = $this->getTagName($tag);

		return $this->getSgrStyleSequence($tagName);
	}

	/**
	 * Returns ANSI SGR escape sequence for style reset and
	 * ANSI SGR escape sequence for parent style if the closed tag was nested.
	 */
	protected function closeStyle(string $tag): string
	{
		if ($this->getTagName($tag) !== array_last($this->openTags)) {
			throw new FormatterException('Detected incorrectly nested formatting tag.');
		}

		// Pop the tag off the array of open tags

		array_pop($this->openTags);

		// Reset style

		$style = $this->getSgrResetSequence();

		// Append previous styles if the closed tag was nested

		if (!empty($this->openTags)) {
			foreach ($this->openTags as $tag) {
				$style .= $this->getSgrStyleSequence($tag);
			}
		}

		// Return style

		return $style;
	}

	/**
	 * Strips escape character from escaped tags.
	 */
	protected function removeTagEscapeCharacter(string $string): string
	{
		return preg_replace(static::ESCAPED_TAG_REGEX, '<$1>', $string);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function format(string $string): string
	{
		// Reset open tags

		$this->openTags = [];

		// Continue with string formatting

		$offset = 0;

		$formatted = '';

		if (preg_match_all(static::TAG_REGEX, $string, $matches, PREG_OFFSET_CAPTURE) > 0) {
			foreach ($matches[0] as $match) {
				[$tag, $pos] = $match;

				$formatted .= substr($string, $offset, $pos - $offset);

				$offset = $pos + strlen($tag);

				if ($this->isOpeningTag($tag)) {
					$formatted .= $this->openStyle($tag);
				}
				else {
					$formatted .= $this->closeStyle($tag);
				}
			}

			if (!empty($this->openTags)) {
				throw new FormatterException('Detected missing formatting close tag.');
			}
		}

		$formatted .= substr($string, $offset);

		return $this->removeTagEscapeCharacter($formatted);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function escape(string $string): string
	{
		return preg_replace(static::TAG_REGEX, '\\\$0', $string);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function stripTags(string $string): string
	{
		return preg_replace(static::TAG_REGEX, '', $string);
	}

	/**
	 * Returns a string where all SGR sequences have been stripped.
	 */
	public function stripSGR(string $string): string
	{
		return preg_replace(static::ANSI_SGR_SEQUENCE_REGEX, '', $string);
	}
}
