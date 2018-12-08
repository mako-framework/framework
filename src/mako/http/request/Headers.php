<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use ArrayIterator;
use Countable;
use IteratorAggregate;

use function array_merge;
use function array_values;
use function count;
use function explode;
use function krsort;
use function str_replace;
use function strpos;
use function strtoupper;
use function substr;
use function trim;

/**
 * Headers.
 *
 * @author Frederic G. Østby
 */
class Headers implements Countable, IteratorAggregate
{
	/**
	 * Headers.
	 *
	 * @var array
	 */
	protected $headers;

	/**
	 * Acceptable content types.
	 *
	 * @var array
	 */
	protected $acceptableContentTypes;

	/**
	 * Acceptable languages.
	 *
	 * @var array
	 */
	protected $acceptableLanguages;

	/**
	 * Acceptable character sets.
	 *
	 * @var array
	 */
	protected $acceptableCharsets;

	/**
	 * Acceptable encodings.
	 *
	 * @var array
	 */
	protected $acceptableEncodings;

	/**
	 * Constructor.
	 *
	 * @param array $headers Headers
	 */
	public function __construct(array $headers = [])
	{
		$this->headers = $headers;
	}

	/**
	 * Returns the numner of headers.
	 *
	 * @return int
	 */
	public function count(): int
	{
		return count($this->headers);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->headers);
	}

	/**
	 * Normalizes header names.
	 *
	 * @param  string $name Header name
	 * @return string
	 */
	protected function normalizeName(string $name): string
	{
		return strtoupper(str_replace('-', '_', $name));
	}

	/**
	 * Adds a header.
	 *
	 * @param string $name  Header name
	 * @param string $value Header value
	 */
	public function add(string $name, string $value)
	{
		$this->headers[$this->normalizeName($name)] = $value;
	}

	/**
	 * Returns true if the header exists and false if not.
	 *
	 * @param  string $name Header name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		return isset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Gets a header value.
	 *
	 * @param  string $name    Header name
	 * @param  mixed  $default Default value
	 * @return mixed
	 */
	public function get(string $name, $default = null)
	{
		return $this->headers[$this->normalizeName($name)] ?? $default;
	}

	/**
	 * Removes a header.
	 *
	 * @param string $name Header name
	 */
	public function remove(string $name)
	{
		unset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Returns all the headers.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->headers;
	}

	/**
	 * Parses a accpet header and returns the values in descending order of preference.
	 *
	 * @param  string|null $headerValue Header value
	 * @return array
	 */
	protected function parseAcceptHeader(?string $headerValue = null): array
	{
		$groupedAccepts = [];

		if(empty($headerValue))
		{
			return $groupedAccepts;
		}

		// Collect acceptable values

		foreach(explode(',', $headerValue) as $accept)
		{
			$quality = 1;

			if(strpos($accept, ';'))
			{
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
	 *
	 * @param  string|null $default Default content type
	 * @return array
	 */
	public function acceptableContentTypes(?string $default = null): array
	{
		if(!isset($this->acceptableContentTypes))
		{
			$this->acceptableContentTypes = $this->parseAcceptHeader($this->get('accept'));
		}

		return $this->acceptableContentTypes ?: (array) $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @param  string|null $default Default language
	 * @return array
	 */
	public function acceptableLanguages(?string $default = null): array
	{
		if(!isset($this->acceptableLanguages))
		{
			$this->acceptableLanguages = $this->parseAcceptHeader($this->get('accept-language'));
		}

		return $this->acceptableLanguages ?: (array) $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @param  string|null $default Default charset
	 * @return array
	 */
	public function acceptableCharsets(?string $default = null): array
	{
		if(!isset($this->acceptableCharsets))
		{
			$this->acceptableCharsets = $this->parseAcceptHeader($this->get('accept-charset'));
		}

		return $this->acceptableCharsets ?: (array) $default;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @param  string|null $default Default encoding
	 * @return array
	 */
	public function acceptableEncodings(?string $default = null): array
	{
		if(!isset($this->acceptableEncodings))
		{
			$this->acceptableEncodings = $this->parseAcceptHeader($this->get('accept-encoding'));
		}

		return $this->acceptableEncodings ?: (array) $default;
	}
}
