<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;

use function array_merge;
use function array_values;
use function count;
use function explode;
use function krsort;
use function str_contains;
use function str_replace;
use function stripos;
use function strtoupper;
use function substr;
use function trim;

/**
 * Headers.
 */
class Headers implements Countable, IteratorAggregate
{
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
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->headers);
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
	 * Returns TRUE if the header exists and FALSE if not.
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
	 * Parses a accpet header and returns the values in descending order of preference.
	 */
	protected function parseAcceptHeader(?string $headerValue): array
	{
		$groupedAccepts = [];

		if (empty($headerValue)) {
			return $groupedAccepts;
		}

		// Collect acceptable values

		foreach (explode(',', $headerValue) as $accept) {
			$quality = 1;

			if (str_contains($accept, ';')) {
				// We have a quality so we need to split some more

				[$accept, $quality] = explode(';', $accept, 2);

				// Strip the "q=" part so that we're left with only the numeric value

				$quality = substr(trim($quality), 2);
			}

			$groupedAccepts[$quality][] = trim($accept);
		}

		// Sort in descending order of preference

		krsort($groupedAccepts);

		// Flatten array and return it

		return array_merge(...array_values($groupedAccepts));
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
	 * Returns the bearer token or NULL if there isn't one.
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
