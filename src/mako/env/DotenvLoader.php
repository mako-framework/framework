<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\env;

use mako\env\exceptions\EnvException;

use function array_key_exists;
use function ctype_space;
use function file;
use function getenv;
use function mako\env;
use function preg_match;
use function preg_replace_callback;
use function putenv;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strpos;
use function substr;
use function trim;

/**
 * Dotenv loader.
 */
final class DotenvLoader
{
	/**
	 * Strip inline comment from value.
	 */
	private static function stripInlineComment(string $value): string
	{
		$quote = ($value[0] === '"' || $value[0] === "'") ? $value[0] : null;
		$escaped = false;

		$length = strlen($value);

		for ($i = 0; $i < $length; $i++) {
			$char = $value[$i];

			if ($quote !== null) {
				if ($i === 0) {
					continue;
				}

				if ($quote === '"' && $char === '\\' && !$escaped) {
					$escaped = true;
					continue;
				}

				if ($char === $quote && !$escaped) {
					$quote = null;
					continue;
				}

				$escaped = false;
				continue;
			}

			if ($char === '#' && $i > 0 && ctype_space($value[$i - 1])) {
				return rtrim(substr($value, 0, $i));
			}
		}

		return $value;
	}

	/**
	 * Expand variables in value.
	 */
	private static function expandVariables(string $value): string
	{
		return preg_replace_callback(
			'/(\\\\*)\$\{([A-Z_][A-Z0-9_]*)\}/i',
			static function (array $matches): string {
				$slashes = $matches[1];
				$key = $matches[2];

				// Odd number of backslashes escapes the variable

				if ((strlen($slashes) % 2) === 1) {
					return substr($slashes, 1) . "\${{$key}}";
				}

				// Even number of backslashes, we'll expand the variable

				return $slashes . (env($key) ?? throw new EnvException(sprintf('Unknown environment variable [ $%s ].', $key)));
			},
			$value
		) ?? $value;
	}

	/**
	 * Unescape values.
	 */
	private static function unescape(string $value): string
	{
		return str_replace(
			['\\n', '\\r', '\\t', '\\"', '\\\\'],
			["\n", "\r", "\t", '"', '\\'],
			$value
		);
	}

	/**
	 * Parse value.
	 */
	private static function parseValue(string $value, int $lineNumber, bool $expandVariables): string
	{
		// Remove inline comments

		if (strpos($value, '#') !== false) {
			$value = self::stripInlineComment($value);
		}

		if ($value === '') {
			return '';
		}

		// Get first and last character

		$first = $value[0];
		$last = $value[strlen($value) - 1];

		// Quoted value

		if ($first === '"' || $first === "'") {
			if ($last !== $first) {
				throw new EnvException(sprintf('Unterminated quoted value on line [ %s ]', $lineNumber));
			}

			$value = substr($value, 1, -1);

			if ($first === '"') {
				if ($expandVariables && strpos($value, '${') !== false) {
					$value = self::expandVariables($value);
				}

				$value = self::unescape($value);
			}

			return $value;
		}

		// Unquoted value so we'll just trim it and return

		return trim($value);
	}

	/**
	 * Loads the file into environment variables.
	 */
	public static function load(string $filePath, bool $overrideExisting = false, bool $expandVariables = false, bool $usePutEnv = false): void
	{
		$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if ($lines === false) {
			throw new EnvException(sprintf('Failed to read env file [ %s ].', $filePath));
		}

		foreach ($lines as $key => $line) {
			$lineNumber = $key + 1;

			// Check for NUL byte

			if (str_contains($line, "\0")) {
				throw new EnvException(sprintf('NUL byte detected in [ %s ] on line [ %s ].', $filePath, $lineNumber));
			}

			// Strip UTF-8 BOM from first line

			if ($lineNumber === 1 && str_starts_with($line, "\xEF\xBB\xBF")) {
                $line = substr($line, 3);
            }

			$line = trim($line);

			// Skip empty lines and lines that are comments

			if ($line === '' || str_starts_with($line, '#')) {
				continue;
			}

			// Strip "export" line prefix

			if (str_starts_with($line, 'export ')) {
				$line = trim(substr($line, 7));
			}

			// Split key and value

			$pos = strpos($line, '=');

			if ($pos === false) {
				throw new EnvException(sprintf('Invalid env declaration in [ %s ] on line [ %s ].', $filePath, $lineNumber));
			}

			$key = trim(substr($line, 0, $pos));
			$value = trim(substr($line, $pos + 1));

			if ($key === '' || !preg_match('/^(?:[A-Z][A-Z0-9_]*|_[A-Z0-9_]+)$/i', $key)) {
				throw new EnvException(sprintf('Invalid key [ %s ] in [ %s ] on line [ %s ].', ($key ?: 'empty'), $filePath, $lineNumber));
			}

			// Should we skip overriding existing variables?

			if ($overrideExisting === false && (array_key_exists($key, $_ENV) || getenv($key) !== false)) {
				continue;
			}

			// Parse value and put it into environment

			if ($value !== '') {
				$value = self::parseValue($value, $lineNumber, $expandVariables);
			}

			if ($usePutEnv) {
				putenv("{$key}={$value}");
			}

			$_ENV[$key] = $value;
		}
	}
}
