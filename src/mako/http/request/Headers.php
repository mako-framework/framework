<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use Countable;
use IteratorAggregate;
use Override;
use Traversable;

use function array_column;
use function array_shift;
use function count;
use function explode;
use function preg_match;
use function str_contains;
use function str_replace;
use function strcasecmp;
use function stripos;
use function strlen;
use function strtoupper;
use function substr;
use function trim;
use function usort;

/**
 * Headers.
 */
class Headers implements Countable, IteratorAggregate
{
	/**
	 * Maximum accept header size.
	 */
	protected const int ACCEPT_HEADER_MAX_SIZE = 16384;

	/**
	 * Invalid accept header characters.
	 */
	protected const string ACCEPT_HEADER_DISALLOWED_CONTROL_CHARS_PATTERN = '/[\x00-\x08\x0A-\x1F\x7F]/';

	/**
	 * The maximum allowed number of entries in a accept header.
	 */
	protected const int ACCEPT_HEADER_MAX_ENTRIES = 100;

	/**
	 * Acceptable content types.
	 */
	protected ?array $acceptableContentTypes = null;

	/**
	 * Acceptable languages.
	 */
	protected ?array $acceptableLanguages = null;

	/**
	 * Acceptable character sets.
	 */
	protected ?array $acceptableCharsets = null;

	/**
	 * Acceptable encodings.
	 */
	protected ?array $acceptableEncodings = null;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $headers = []
	) {
	}

	/**
	 * Returns the numner of headers.
	 */
	#[Override]
	public function count(): int
	{
		return count($this->headers);
	}

	/**
	 * Retruns an array iterator object.
	 */
	#[Override]
	public function getIterator(): Traversable
	{
		yield from $this->headers;
	}

	/**
	 * Normalizes header names.
	 */
	protected function normalizeName(string $name): string
	{
		return strtoupper(str_replace('-', '_', $name));
	}

	/**
	 * Adds a header.
	 */
	public function add(string $name, string $value): void
	{
		$this->headers[$this->normalizeName($name)] = $value;
	}

	/**
	 * Returns true if the header exists and false if not.
	 */
	public function has(string $name): bool
	{
		return isset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Gets a header value.
	 */
	public function get(string $name, mixed $default = null): mixed
	{
		return $this->headers[$this->normalizeName($name)] ?? $default;
	}

	/**
	 * Removes a header.
	 */
	public function remove(string $name): void
	{
		unset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Returns all the headers.
	 */
	public function all(): array
	{
		return $this->headers;
	}

	/**
	 * Splits a string on a delimiter while ignoring delimiters inside quoted strings.
	 *
	 * Returns null if the input contains control characters or has an unterminated quote or escape sequence.
	 */
	protected function splitOutsideQuotes(string $input, string $delimiter): ?array
	{
		$parts = [];
		$start = 0;
		$quoted = false;
		$escaped = false;

		for ($i = 0, $length = strlen($input); $i < $length; ++$i) {
			$char = $input[$i];

			if ($escaped) {
				$escaped = false;
			}
			elseif ($quoted && $char === '\\') {
				$escaped = true;
			}
			elseif ($char === '"') {
				$quoted = !$quoted;
			}
			elseif (!$quoted && $char === $delimiter) {
				$parts[] = substr($input, $start, $i - $start);
				$start = $i + 1;
			}
		}

		if ($quoted || $escaped) {
			return null;
		}

		$parts[] = substr($input, $start);

		return $parts;
	}

	/**
	 * Parses an accept header and returns the values in descending order of preference.
	 */
	protected function parseAcceptHeader(?string $headerValue): array
	{
		if (
			$headerValue === null
			|| strlen($headerValue) > static::ACCEPT_HEADER_MAX_SIZE
			|| trim($headerValue) === ''
			|| preg_match(static::ACCEPT_HEADER_DISALLOWED_CONTROL_CHARS_PATTERN, $headerValue)
		) {
			return [];
		}

		$hasQuotes = str_contains($headerValue, '"');

		$entries = $hasQuotes
			? $this->splitOutsideQuotes($headerValue, ',')
			: explode(',', $headerValue, static::ACCEPT_HEADER_MAX_ENTRIES + 1);

		if ($entries === null || count($entries) > static::ACCEPT_HEADER_MAX_ENTRIES) {
			return [];
		}

		$accepts = [];

		foreach ($entries as $accept) {
			$parts = $hasQuotes && str_contains($accept, '"')
				? $this->splitOutsideQuotes($accept, ';')
				: explode(';', $accept);

			if ($parts === null) {
				return [];
			}

			$value = trim(array_shift($parts));

			if ($value === '') {
				continue;
			}

			$quality = 1.0;
			$hasQuality = false;

			foreach ($parts as $parameter) {
				$pair = explode('=', $parameter, 2);

				if (count($pair) !== 2) {
					return [];
				}

				$name = trim($pair[0]);
				$parameterValue = trim($pair[1]);

				if ($name === '' || $parameterValue === '') {
					return [];
				}

				if (strcasecmp($name, 'q') === 0) {
					if (
						$hasQuality
						|| !preg_match('/\A(?:0(?:\.[0-9]{0,3})?|1(?:\.0{0,3})?)\z/', $parameterValue)
					) {
						return [];
					}

					$quality = (float) $parameterValue;
					$hasQuality = true;
				}
			}

			if ($quality > 0) {
				$accepts[] = ['value' => $value, 'quality' => $quality];
			}
		}

		usort(
			$accepts,
			static fn (array $a, array $b): int => $b['quality'] <=> $a['quality']
		);

		return array_column($accepts, 'value');
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 */
	public function getAcceptableContentTypes(?string $default = null): array
	{
		if (!isset($this->acceptableContentTypes)) {
			$this->acceptableContentTypes = $this->parseAcceptHeader($this->get('accept'));
		}

		return $this->acceptableContentTypes ?: (array) $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 */
	public function getAcceptableLanguages(?string $default = null): array
	{
		if ($this->acceptableLanguages === null) {
			$this->acceptableLanguages = $this->parseAcceptHeader($this->get('accept-language'));
		}

		return $this->acceptableLanguages ?: (array) $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 */
	public function getAcceptableCharsets(?string $default = null): array
	{
		if ($this->acceptableCharsets === null) {
			$this->acceptableCharsets = $this->parseAcceptHeader($this->get('accept-charset'));
		}

		return $this->acceptableCharsets ?: (array) $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 */
	public function getAcceptableEncodings(?string $default = null): array
	{
		if ($this->acceptableEncodings === null) {
			$this->acceptableEncodings = $this->parseAcceptHeader($this->get('accept-encoding'));
		}

		return $this->acceptableEncodings ?: (array) $default;
	}

	/**
	 * Returns the bearer token or null if there isn't one.
	 */
	public function getBearerToken(): ?string
	{
		if (($value = $this->get('authorization')) === null) {
			return null;
		}

		if (($pos = stripos($value, 'Bearer ')) === false) {
			return null;
		}

		return substr($value, $pos + 7);
	}
}
