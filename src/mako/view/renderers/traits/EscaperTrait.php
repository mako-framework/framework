<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 * @copyright Pádraic Brady
 * @license   https://raw.githubusercontent.com/padraic/SecurityMultiTool/master/LICENSE
 */

namespace mako\view\renderers\traits;

use function bin2hex;
use function ctype_digit;
use function hexdec;
use function htmlspecialchars;
use function mb_convert_encoding;
use function ord;
use function preg_replace_callback;
use function rawurlencode;
use function sprintf;
use function strlen;
use function strtoupper;

/**
 * Escaper trait.
 *
 * @author Frederic G. Østby
 * @author Pádraic Brady
 */
trait EscaperTrait
{
	/**
	 * HTML entity map.
	 *
	 * @var array
	 */
	protected $htmlNamedEntityMap =
	[
		34 => 'quot',
		38 => 'amp',
		60 => 'lt',
		62 => 'gt',
	];

	/**
	 * Returns a string that has been escaped for a HTML body context.
	 *
	 * @param  string|null $string       String to escape
	 * @param  string      $charset      Character set
	 * @param  bool        $doubleEncode Should existing entities be encoded?
	 * @return string
	 */
	public function escapeHTML(?string $string, string $charset, bool $doubleEncode = true): string
	{
		return htmlspecialchars($string, ENT_QUOTES, $charset, $doubleEncode);
	}

	/**
	 * Returns a string that has been escaped for a URI or parameter context.
	 *
	 * @param  string|null $string String to escape
	 * @return string
	 */
	public function escapeURL(?string $string): string
	{
		return rawurlencode($string);
	}

	/**
	 * Escapes characters for use in a HTML attribute context.
	 *
	 * (This method contains code from the SecurityMultiTool library).
	 *
	 * @param  array  $matches Regex matches
	 * @return string
	 */
	protected function attributeEscaper(array $matches): string
	{
		$chr = $matches[0];

		$ord = ord($chr);

		// The following replaces characters undefined in HTML with the
		// hex entity for the Unicode replacement character.

		if(($ord <= 0x1f && $chr !== "\t" && $chr !== "\n" && $chr !== "\r") || ($ord >= 0x7f && $ord <= 0x9f))
		{
			return '&#xFFFD;';
		}

		// Check if the current character to escape has a name entity we should
		// replace it with while grabbing the integer value of the character.

		if(strlen($chr) > 1)
		{
			$chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
		}

		$hex = bin2hex($chr);

		$ord = hexdec($hex);

		if(isset($this->htmlNamedEntityMap[$ord]))
		{
			return "&{$this->htmlNamedEntityMap[$ord]};";
		}

		// Per OWASP recommendations, we'll use upper hex entities
		// for any other characters where a named entity does not exist.

		if($ord > 255)
		{
			return sprintf('&#x%04X;', $ord);
		}

		return sprintf('&#x%02X;', $ord);
	}

	/**
	 * Returns a string that has been escaped for a HTML attribute context.
	 *
	 * @param  string|null $string  String to escape
	 * @param  string      $charset Character set
	 * @return string
	 */
	public function escapeAttribute(?string $string, string $charset): string
	{
		if($charset !== 'UTF-8')
		{
			$string = mb_convert_encoding($string, 'UTF-8', $charset);
		}

		$string = preg_replace_callback('/[^a-z0-9,\.\-_]/iSu', [$this, 'attributeEscaper'], $string);

		if($charset !== 'UTF-8')
		{
			$string = mb_convert_encoding($string, $charset, 'UTF-8');
		}

		return $string;
	}

	/**
	 * Escapes characters for use in a CSS context.
	 *
	 * (This method contains code from the SecurityMultiTool library).
	 *
	 * @param  array  $matches Regex matches
	 * @return string
	 */
	protected function cssEscaper(array $matches): string
	{
		$chr = $matches[0];

		if(strlen($chr) === 1)
		{
			$ord = ord($chr);
		}
		else
		{
			$chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');

			$ord = hexdec(bin2hex($chr));
		}

		return sprintf('\\%X ', $ord);
	}

	/**
	 * Returns a string that has been escaped for a CSS context.
	 *
	 * @param  string|null $string  String to escape
	 * @param  string      $charset Character set
	 * @return string
	 */
	public function escapeCSS(?string $string, string $charset): string
	{
		if($string === '' || ctype_digit($string))
		{
			return $string;
		}

		if($charset !== 'UTF-8')
		{
			$string = mb_convert_encoding($string, 'UTF-8', $charset);
		}

		$string = preg_replace_callback('/[^a-z0-9]/iSu', [$this, 'cssEscaper'], $string);

		if($charset !== 'UTF-8')
		{
			$string = mb_convert_encoding($string, $charset, 'UTF-8');
		}

		return $string;
	}

	/**
	 * Escapes characters for use in a Javascript context.
	 *
	 * (This method contains code from the SecurityMultiTool library).
	 *
	 * @param  array  $matches Regex matches
	 * @return string
	 */
	protected function javascriptEscaper(array $matches): string
	{
		$chr = $matches[0];

		if(strlen($chr) === 1)
		{
			return sprintf('\\x%02X', ord($chr));
		}

		$chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');

		return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
	}

	/**
	 * Returns a string that has been escaped for a Javascript context.
	 *
	 * @param  string|null $string  String to escape
	 * @param  string      $charset Character set
	 * @return string
	 */
	public function escapeJavascript(?string $string, string $charset): string
	{
		if($charset !== 'UTF-8')
		{
			$string = mb_convert_encoding($string, 'UTF-8', $charset);
		}

		$string = preg_replace_callback('/[^a-z0-9,\._]/iSu', [$this, 'javascriptEscaper'], $string);

		if($charset !== 'UTF-8')
		{
			$string = mb_convert_encoding($string, $charset, 'UTF-8');
		}

		return $string;
	}
}
