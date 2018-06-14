<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use Closure;

/**
 * Collection of string manipulation methods.
 *
 * @author Frederic G. Østby
 */
class Str
{
	/**
	 * Alphanumeric characters.
	 *
	 * @var string
	 */
	const ALNUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Alphabetic characters.
	 *
	 * @var string
	 */
	const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Hexadecimal characters.
	 *
	 * @var string
	 */
	const HEXDEC = '0123456789abcdef';

	/**
	 * Numeric characters.
	 *
	 * @var string
	 */
	const NUMERIC = '0123456789';

	/**
	 * ASCII symbols.
	 *
	 * @var string
	 */
	const SYMBOLS = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

	/**
	 * Pluralization rules.
	 *
	 * @var array
	 */
	protected static $pluralizationRules =
	[
		'/(quiz)$/i'                     => '$1zes',
		'/([m|l])ouse$/i'                => '$1ice',
		'/(.+)(e|i)x$/'                  => '$1ices',
		'/(z|x|ch|ss|sh)$/i'             => '$1es',
		'/([^aeiouy]|qu)y$/i'            => '$1ies',
		'/(hive)$/i'                     => '$1s',
		'/(?:([^f])fe|([lr])f)$/i'       => '$1$2ves',
		'/(shea|lea|loa|thie)f$/i'       => '$1ves',
		'/sis$/i'                        => 'ses',
		'/([ti])um$/i'                   => '$1a',
		'/(tomat|potat|ech|her|vet)o$/i' => '$1oes',
		'/(bu)s$/i'                      => '$1ses',
		'/(octop|vir)us$/'               => '$1i',
		'/(ax|test)is$/i'                => '$1es',
		'/(us)$/i'                       => '$1es',
		'/((.*)(?<!hu))man$/i'           => '$1men',
		'/s$/i'                          => 's',
		'/$/'                            => 's',
	];

	/**
	 * Irregular nouns.
	 *
	 * @var array
	 */
	protected static $irregulars =
	[
		'alias'       => 'aliases',
		'audio'       => 'audio',
		'child'       => 'children',
		'deer'        => 'deer',
		'equipment'   => 'equipment',
		'fish'        => 'fish',
		'foot'        => 'feet',
		'furniture'   => 'furniture',
		'gold'        => 'gold',
		'goose'       => 'geese',
		'hardware'    => 'hardware',
		'information' => 'information',
		'money'       => 'money',
		'ox'          => 'oxen',
		'police'      => 'police',
		'series'      => 'series',
		'sex'         => 'sexes',
		'sheep'       => 'sheep',
		'software'    => 'software',
		'species'     => 'species',
		'tooth'       => 'teeth',
	];

	/**
	 * Replaces newline with <br> or <br />.
	 *
	 * @param  string $string The input string
	 * @param  bool   $xhtml  Should we return XHTML?
	 * @return string
	 */
	public static function nl2br(string $string, bool $xhtml = false): string
	{
		return str_replace(["\r\n", "\n\r", "\n", "\r"], (new HTML($xhtml))->tag('br'), $string);
	}

	/**
	 * Replaces <br> and <br /> with newline.
	 *
	 * @param  string $string The input string
	 * @return string
	 */
	public static function br2nl(string $string): string
	{
		return str_replace(['<br>', '<br/>', '<br />'], "\n", $string);
	}

	/**
	 * Returns the plural form of a noun (english only).
	 *
	 * @param  string   $noun  Noun to pluralize
	 * @param  int|null $count Number of nouns
	 * @return string
	 */
	public static function pluralize(string $noun, int $count = null): string
	{
		if($count !== 1)
		{
			if(isset(static::$irregulars[$noun]))
			{
				$noun = static::$irregulars[$noun];
			}
			else
			{
				foreach(static::$pluralizationRules as $search => $replace)
				{
					if(preg_match($search, $noun))
					{
						$noun = preg_replace($search, $replace, $noun);

						break;
					}
				}
			}
		}

		return $noun;
	}

	/**
	 * Converts camel case to underscored.
	 *
	 * @param  string $string The input string
	 * @return string
	 */
	public static function camel2underscored(string $string): string
	{
		return mb_strtolower(preg_replace('/([^A-Z])([A-Z])/u', '$1_$2', $string));
	}

	/**
	 * Converts underscored to camel case.
	 *
	 * @param  string $string The input string
	 * @param  bool   $upper  Return upper case camelCase?
	 * @return string
	 */
	public static function underscored2camel(string $string, bool $upper = false): string
	{
		return preg_replace_callback(($upper ? '/(?:^|_)(.?)/u' : '/_(.?)/u'), function($matches) { return mb_strtoupper($matches[1]); }, $string);
	}

	/**
	 * Limits the number of characters in a string.
	 *
	 * @param  string $string     The input string
	 * @param  int    $characters Number of characters to allow
	 * @param  string $sufix      Sufix to add if number of characters is reduced
	 * @return string
	 */
	public static function limitChars(string $string, int $characters = 100, string $sufix = '...'): string
	{
		return (mb_strlen($string) > $characters) ? trim(mb_substr($string, 0, $characters)) . $sufix : $string;
	}

	/**
	 * Limits the number of words in a string.
	 *
	 * @param  string $string The input string
	 * @param  int    $words  Number of words to allow
	 * @param  string $sufix  Sufix to add if number of words is reduced
	 * @return string
	 */
	public static function limitWords(string $string, int $words = 100, string $sufix = '...'): string
	{
		preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/', $string, $matches);

		if(isset($matches[0]) && mb_strlen($matches[0]) < mb_strlen($string))
		{
			return trim($matches[0]) . $sufix;
		}

		return $string;
	}

	/**
	 * Creates a URL friendly string.
	 *
	 * @param  string $string The input string
	 * @return string
	 */
	public static function slug(string $string): string
	{
		return rawurlencode(mb_strtolower(preg_replace('/\s{1,}/', '-', trim(preg_replace('/[\x0-\x1F\x21-\x2C\x2E-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]/', '', $string)))));
	}

	/**
	 * Strips all non-ASCII characters.
	 *
	 * @param  string $string The input string
	 * @return string
	 */
	public static function ascii(string $string): string
	{
		return preg_replace('/[^\x0-\x7F]/', '', $string);
	}

	/**
	 * Returns a closure that will alternate between the defined strings.
	 *
	 * @param  array    $strings Array of strings to alternate between
	 * @return \Closure
	 */
	public static function alternator(array $strings): Closure
	{
		return function() use ($strings)
		{
			static $i = 0;

			return $strings[($i++ % count($strings))];
		};
	}

	/**
	 * Converts URLs in a text into clickable links.
	 *
	 * @param  string $string     Text to scan for links
	 * @param  array  $attributes Anchor attributes
	 * @return string
	 */
	public static function autolink(string $string, array $attributes = []): string
	{
		return preg_replace_callback('#\b(?<!href="|">)[a-z]+://\S+(?:/|\b)#i', function($matches) use ($attributes)
		{
			return (new HTML())->tag('a', ['href' => $matches[0]] + $attributes, $matches[0]);
		}, $string);
	}

	/**
	 * Returns a masked string where only the last n characters are visible.
	 *
	 * @param  string $string  String to mask
	 * @param  int    $visible Number of characters to show
	 * @param  string $mask    Character used to replace remaining characters
	 * @return string
	 */
	public static function mask(string $string, int $visible = 3, string $mask = '*'): string
	{
		if($visible === 0)
		{
			return str_repeat($mask, mb_strlen($string));
		}

		$visible = mb_substr($string, -$visible);

		return str_pad($visible, (mb_strlen($string) + (strlen($visible) - mb_strlen($visible))), $mask, STR_PAD_LEFT);
	}

	/**
	 * Increments a string by appending a number to it or increasing the number.
	 *
	 * @param  string $string    String to increment
	 * @param  int    $start     Starting number
	 * @param  string $separator Separator
	 * @return string
	 */
	public static function increment(string $string, int $start = 1, string $separator = '_'): string
	{
		preg_match('/(.+)' . preg_quote($separator) . '([0-9]+)$/', $string, $matches);

		return isset($matches[2]) ? $matches[1] . $separator . ((int) $matches[2] + 1) : $string . $separator . $start;
	}

	/**
	 * Returns a random string of the selected type and length.
	 *
	 * @param  string $pool   Character pool to use
	 * @param  int    $length Desired string length
	 * @return string
	 */
	public static function random(string $pool = Str::ALNUM, int $length = 32): string
	{
		$string = '';

		$poolSize = mb_strlen($pool) - 1;

		for($i = 0; $i < $length; $i++)
		{
			$string .= mb_substr($pool, random_int(0, $poolSize), 1);
		}

		return $string;
	}
}
