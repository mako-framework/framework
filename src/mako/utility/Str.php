<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use mako\utility\str\Alternator;

use function mb_strlen;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function preg_replace_callback;
use function random_int;
use function rawurlencode;
use function str_pad;
use function str_repeat;
use function str_replace;
use function strlen;
use function trim;

/**
 * Collection of string manipulation methods.
 */
class Str
{
	/**
	 * Alphanumeric characters.
	 *
	 * @var string
	 */
	public const ALNUM = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Alphabetic characters.
	 *
	 * @var string
	 */
	public const ALPHA = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Hexadecimal characters.
	 *
	 * @var string
	 */
	public const HEXDEC = '0123456789abcdef';

	/**
	 * Numeric characters.
	 *
	 * @var string
	 */
	public const NUMERIC = '0123456789';

	/**
	 * ASCII symbols.
	 *
	 * @var string
	 */
	public const SYMBOLS = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

	/**
	 * Pluralization rules.
	 */
	protected static array $pluralizationRules =
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
	 */
	protected static array $irregulars =
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
	 */
	public static function nl2br(string $string, bool $xhtml = false): string
	{
		return str_replace(["\r\n", "\n\r", "\n", "\r"], (new HTML($xhtml))->tag('br'), $string);
	}

	/**
	 * Replaces <br> and <br /> with newline.
	 */
	public static function br2nl(string $string): string
	{
		return str_replace(['<br>', '<br/>', '<br />'], "\n", $string);
	}

	/**
	 * Returns the plural form of a noun (english only).
	 */
	public static function pluralize(string $noun, ?int $count = null): string
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
	 */
	public static function camelToSnake(string $string): string
	{
		return mb_strtolower(preg_replace('/([^A-Z])([A-Z])/u', '$1_$2', $string));
	}

	/**
	 * Converts underscored to camel case.
	 */
	public static function snakeToCamel(string $string, bool $upper = false): string
	{
		return preg_replace_callback(($upper ? '/(?:^|_)(.?)/u' : '/_(.?)/u'), static fn ($matches) => mb_strtoupper($matches[1]), $string);
	}

	/**
	 * Limits the number of characters in a string.
	 */
	public static function limitChars(string $string, int $characters = 100, string $sufix = '...'): string
	{
		return (mb_strlen($string) > $characters) ? trim(mb_substr($string, 0, $characters)) . $sufix : $string;
	}

	/**
	 * Limits the number of words in a string.
	 */
	public static function limitWords(string $string, int $words = 100, string $sufix = '...'): string
	{
		preg_match("/^\s*+(?:\S++\s*+){1,{$words}}/", $string, $matches);

		if(isset($matches[0]) && mb_strlen($matches[0]) < mb_strlen($string))
		{
			return trim($matches[0]) . $sufix;
		}

		return $string;
	}

	/**
	 * Creates a URL friendly string.
	 */
	public static function slug(string $string): string
	{
		return rawurlencode(mb_strtolower(preg_replace('/\s{1,}/', '-', trim(preg_replace('/[\x0-\x1F\x21-\x2C\x2E-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]/', '', $string)))));
	}

	/**
	 * Strips all non-ASCII characters.
	 */
	public static function ascii(string $string): string
	{
		return preg_replace('/[^\x0-\x7F]/', '', $string);
	}

	/**
	 * Returns a alternator that will alternate between the defined strings.
	 */
	public static function alternator(array $strings): Alternator
	{
		return new Alternator($strings);
	}

	/**
	 * Converts URLs in a text into clickable links.
	 */
	public static function autolink(string $string, array $attributes = []): string
	{
		return preg_replace_callback('#\b(?<!href="|">)[a-z]+://\S+(?:/|\b)#i', static fn ($matches) => (new HTML())->tag('a', ['href' => $matches[0]] + $attributes, $matches[0]), $string);
	}

	/**
	 * Returns a masked string where only the last n characters are visible.
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
	 */
	public static function increment(string $string, int $start = 1, string $separator = '_'): string
	{
		preg_match('/(.+)' . preg_quote($separator) . '([0-9]+)$/', $string, $matches);

		return isset($matches[2]) ? "{$matches[1]}{$separator}" . ((int) $matches[2] + 1) : "{$string}{$separator}{$start}";
	}

	/**
	 * Returns a random string of the selected type and length.
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
