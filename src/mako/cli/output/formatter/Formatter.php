<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cli\output\formatter;

use mako\cli\output\formatter\FormatterException;
use mako\cli\output\formatter\FormatterInterface;

/**
 * Formatter.
 *
 * @author  Frederic G. Østby
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
	 * Styles.
	 * 
	 * @var array 
	 */

	protected $styles = 
	[
		'bold'       => 1,
		'faded'      => 2,
		'underlined' => 4,
		'blinking'   => 5,
		'reversed'   => 7,
		'hidden'     => 8,

		'black'      => 30,
		'red'        => 31,
		'green'      => 32,
		'yellow'     => 33,
		'blue'       => 34,
		'purple'     => 35,
		'cyan'       => 36,
		'white'      => 37,

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
	 * Do we have ANSI support?
	 * 
	 * @var boolean
	 */

	protected $hasAnsiSupport;

	/**
	 * User styles.
	 * 
	 * @var array
	 */

	protected $userStyles = [];

	/**
	 * Open tags.
	 * 
	 * @var arary
	 */

	protected $openTags = [];

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   null|boolean  $hasAnsiSupport  Do we have ANSI support?
	 */

	public function __construct($hasAnsiSupport = null)
	{
		if($hasAnsiSupport === null)
		{
			$hasAnsiSupport = DIRECTORY_SEPARATOR === '/' || (false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI'));
		}

		$this->hasAnsiSupport = $hasAnsiSupport;
	}

	/**
	 * Adds a user defined style.
	 * 
	 * @access  public
	 * @param   string        $name   Style name
	 * @param   string|array  $style  Style or array of styles
	 */

	public function addStyle($name, $style)
	{
		$this->userStyles[$name] = (array) $style;
	}

	/**
	 * Returns the tag name.
	 * 
	 * @access  protected
	 * @param   string     $tag  Tag
	 * @return  string
	 */

	protected function getTagName($tag)
	{
		return str_replace(['<', '>', '/'], '', $tag);
	}

	/**
	 * Returns TRUE if the tag is a closing tag and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $tag  Tag to check
	 * @return  boolean
	 */

	protected function isOpeningTag($tag)
	{
		return strpos($tag, '</') === false;
	}

	/**
	 * Returns ANSI code for the style opening.
	 * 
	 * @access  protected
	 * @param   string     $tag  Style name
	 * @return  string
	 */

	protected function getOpenStyle($tag)
	{
		if(isset($this->styles[$tag]))
		{
			return sprintf("\033[%sm", $this->styles[$tag]); 
		}
		elseif(isset($this->userStyles[$tag]))
		{
			$style = '';

			foreach($this->userStyles[$tag] as $tag)
			{
				$style .= $this->getOpenStyle($tag);
			}

			return $style;
		}

		throw new FormatterException(vsprintf("%s(): Undefined formatting tag [ %s ] detected.", [__METHOD__, $tag]));
	}

	/**
	 * Returns ANSI code for the style opening.
	 * 
	 * @access  protected
	 * @param   string     $tag  Style name
	 * @return  string
	 */

	protected function getCloseStyle()
	{
		return "\033[0m";
	}

	/**
	 * Returns ANSI code for the style opening and adds the tag to the array of open tags.
	 * 
	 * @access  protected
	 * @param   string     $tag  Tag name
	 * @return  string
	 */

	protected function openStyle($tag)
	{
		$this->openTags[] = $tagName = $this->getTagName($tag);

		return $this->getOpenStyle($tagName);
	}

	/**
	 * Returns ANSI code for style reset and ANSI code for parent style start if the closed tag was nested.
	 * 
	 * @access  protected
	 * @param   string     $tag  Tag name
	 * @return  string
	 */

	protected function closeStyle($tag)
	{
		if($this->getTagName($tag) !== end($this->openTags))
		{
			throw new FormatterException(vsprintf("%s(): Incorrectly nested formatting tag detected.", [__METHOD__]));
		}

		// Pop the tag off the array of open tags

		array_pop($this->openTags);

		// Reset style and append previous style if the closed tag was nested

		return $this->getCloseStyle() . (!empty($this->openTags) ? $this->getOpenStyle(end($this->openTags)) : '');
	}

	/**
	 * Strips escape character from escaped tags.
	 * 
	 * @access  protected
	 * @param   string     $string  Input string
	 * @return  string
	 */

	protected function removeTagEscapeCharacter($string)
	{
		return preg_replace(static::ESCAPED_TAG_REGEX, '<$1>', $string);
	}

	/**
	 * {@inheritdoc}
	 */

	public function format($string)
	{
		$offset = 0;

		$formatted = '';

		preg_match_all(static::TAG_REGEX, $string, $matches, PREG_OFFSET_CAPTURE);

		foreach($matches[0] as $key => $match)
		{
			list($tag, $pos) = $match;

			$formatted .= substr($string, $offset, $pos - $offset);

			$offset = $pos + strlen($tag);

			if($this->hasAnsiSupport)
			{
				if($this->isOpeningTag($tag))
				{
					$formatted .= $this->openStyle($tag);
				}
				else
				{
					$formatted .= $this->closeStyle($tag);
				}
			}
		}

		if(!empty($this->openTags))
		{
			throw new FormatterException(vsprintf("%s(): 'Missing formatting close tag detected.'", [__METHOD__]));
		}

		$formatted .= substr($string, $offset, $offset);

		return $this->removeTagEscapeCharacter($formatted);
	}

	/**
	 * {@inheritdoc}
	 */

	public function stripFormatting($string)
	{
		return preg_replace("/\033\[[0-9]{1,2}m/", '', $this->format($string));
	}
}