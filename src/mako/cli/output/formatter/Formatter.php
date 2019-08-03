<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\output\formatter;

use function array_merge;
use function array_pop;
use function end;
use function implode;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function vsprintf;

/**
 * Formatter.
 *
 * @author Frederic G. Østby
 */
class Formatter implements FormatterInterface
{
	/**
	 * Regex that matches non-escaped tags.
	 *
	 * @var string
	 */
	const TAG_REGEX = '/(?<!\\\\)<\/?[a-z_]+\>/i';

	/**
	 * Regex that matches escaped tags.
	 *
	 * @var string
	 */
	const ESCAPED_TAG_REGEX = '/\\\\<(\/?[a-z_]+)\>/i';

	/**
	 * Regex that mathes ANSI SGR sequences.
	 *
	 * @var string
	 */
	const ANSI_SGR_SEQUENCE_REGEX = "/\033\[([0-9]{1,2};?)+m/";

	/**
	 * Styles.
	 *
	 * @var array
	 */
	protected $styles =
	[
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
	 *
	 * @var array
	 */
	protected $userStyles = [];

	/**
	 * Open tags.
	 *
	 * @var array
	 */
	protected $openTags = [];

	/**
	 * Adds a user defined style.
	 *
	 * @param string       $name  Style name
	 * @param string|array $style Style or array of styles
	 */
	public function addStyle(string $name, $style): void
	{
		$this->userStyles[$name] = (array) $style;
	}

	/**
	 * Returns the tag name.
	 *
	 * @param  string $tag Tag
	 * @return string
	 */
	protected function getTagName(string $tag): string
	{
		return str_replace(['<', '>', '/'], '', $tag);
	}

	/**
	 * Returns TRUE if the tag is a closing tag and FALSE if not.
	 *
	 * @param  string $tag Tag to check
	 * @return bool
	 */
	protected function isOpeningTag(string $tag): bool
	{
		return strpos($tag, '</') === false;
	}

	/**
	 * Returns ANSI SGR escape sequence for style reset.
	 *
	 * @return string
	 */
	protected function getSgrResetSequence(): string
	{
		return "\033[0m";
	}

	/**
	 * Returns style codes associated with the tag name.
	 *
	 * @param  string                                        $tag Tag name
	 * @throws \mako\cli\output\formatter\FormatterException
	 * @return array
	 */
	protected function getStyleCodes(string $tag): array
	{
		if(isset($this->styles[$tag]))
		{
			return [$this->styles[$tag]];
		}
		elseif(isset($this->userStyles[$tag]))
		{
			$codes = [];

			foreach($this->userStyles[$tag] as $tag)
			{
				$codes = array_merge($codes, $this->getStyleCodes($tag));
			}

			return $codes;
		}

		throw new FormatterException(vsprintf('Undefined formatting tag [ %s ] detected.', [$tag]));
	}

	/**
	 * Returns ANSI SGR escape sequence for the chosen style(s).
	 *
	 * @param  string $tag Style name
	 * @return string
	 */
	protected function getSgrStyleSequence(string $tag): string
	{
		$styles = implode(';', $this->getStyleCodes($tag));

		return sprintf("\033[%sm", $styles);
	}

	/**
	 * Returns ANSI SGR escape sequence(s) for the chosen style(s) and
	 * adds the tag name to the array of open tags.
	 *
	 * @param  string $tag Tag name
	 * @return string
	 */
	protected function openStyle(string $tag): string
	{
		$this->openTags[] = $tagName = $this->getTagName($tag);

		return $this->getSgrStyleSequence($tagName);
	}

	/**
	 * Returns ANSI SGR escape sequence for style reset and
	 * ANSI SGR escape sequence for parent style if the closed tag was nested.
	 *
	 * @param  string                                        $tag Tag name
	 * @throws \mako\cli\output\formatter\FormatterException
	 * @return string
	 */
	protected function closeStyle(string $tag): string
	{
		if($this->getTagName($tag) !== end($this->openTags))
		{
			throw new FormatterException('Detected incorrectly nested formatting tag.');
		}

		// Pop the tag off the array of open tags

		array_pop($this->openTags);

		// Reset style

		$style = $this->getSgrResetSequence();

		// Append previous styles if the closed tag was nested

		if(!empty($this->openTags))
		{
			foreach($this->openTags as $tag)
			{
				$style .= $this->getSgrStyleSequence($tag);
			}
		}

		// Return style

		return $style;
	}

	/**
	 * Strips escape character from escaped tags.
	 *
	 * @param  string $string Input string
	 * @return string
	 */
	protected function removeTagEscapeCharacter(string $string): string
	{
		return preg_replace(static::ESCAPED_TAG_REGEX, '<$1>', $string);
	}

	/**
	 * {@inheritdoc}
	 */
	public function format(string $string): string
	{
		// Reset open tags

		$this->openTags = [];

		// Continue with string formatting

		$offset = 0;

		$formatted = '';

		if(preg_match_all(static::TAG_REGEX, $string, $matches, PREG_OFFSET_CAPTURE) > 0)
		{
			foreach($matches[0] as $match)
			{
				[$tag, $pos] = $match;

				$formatted .= substr($string, $offset, $pos - $offset);

				$offset = $pos + strlen($tag);

				if($this->isOpeningTag($tag))
				{
					$formatted .= $this->openStyle($tag);
				}
				else
				{
					$formatted .= $this->closeStyle($tag);
				}
			}

			if(!empty($this->openTags))
			{
				throw new FormatterException('Detected missing formatting close tag.');
			}
		}

		$formatted .= substr($string, $offset);

		return $this->removeTagEscapeCharacter($formatted);
	}

	/**
	 * {@inheritdoc}
	 */
	public function escape(string $string): string
	{
		return preg_replace(static::TAG_REGEX, '\\\$0', $string);
	}

	/**
	 * {@inheritdoc}
	 */
	public function stripTags(string $string): string
	{
		return preg_replace(static::TAG_REGEX, '', $string);
	}

	/**
	 * Returns a string where all SGR sequences have been stripped.
	 *
	 * @param  string $string String to strip
	 * @return string
	 */
	public function stripSGR(string $string): string
	{
		return preg_replace(static::ANSI_SGR_SEQUENCE_REGEX, '', $string);
	}
}
