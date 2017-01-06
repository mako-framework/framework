<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use ArrayIterator;
use Countable;
use IteratorAggregate;

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
	 * @access public
	 * @param array $headers Headers
	 */
	public function __construct(array $headers = [])
	{
		$this->headers = $headers;
	}

	/**
	 * Returns the numner of headers.
	 *
	 * @access public
	 * @return int
	 */
	public function count(): int
	{
		return count($this->headers);
	}

	/**
	 * Retruns an array iterator object.
	 *
	 * @access public
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
	 * @access public
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
	 * @access public
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
	 * @access public
	 * @param  string     $name    Header name
	 * @param  null|mixed $default Default value
	 * @return null|mixed
	 */
	public function get(string $name, $default = null)
	{
		return $this->headers[$this->normalizeName($name)] ?? $default;
	}

	/**
	 * Removes a header.
	 *
	 * @access public
	 * @param string $name Header name
	 */
	public function remove(string $name)
	{
		unset($this->headers[$this->normalizeName($name)]);
	}

	/**
	 * Returns all the headers.
	 *
	 * @access public
	 * @return array
	 */
	public function all(): array
	{
		return $this->headers;
	}

	/**
	 * Parses a accpet header and returns the values in descending order of preference.
	 *
	 * @access protected
	 * @param  null|string $headerValue Header value
	 * @return array
	 */
	protected function parseAcceptHeader(string $headerValue = null): array
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

				list($accept, $quality) = explode(';', $accept, 2);

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
	 * @access public
	 * @return array
	 */
	public function acceptableContentTypes(): array
	{
		if(!isset($this->acceptableContentTypes))
		{
			$this->acceptableContentTypes = $this->parseAcceptHeader($this->get('accept'));
		}

		return $this->acceptableContentTypes;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access public
	 * @return array
	 */
	public function acceptableLanguages(): array
	{
		if(!isset($this->acceptableLanguages))
		{
			$this->acceptableLanguages = $this->parseAcceptHeader($this->get('accept-language'));
		}

		return $this->acceptableLanguages;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access public
	 * @return array
	 */
	public function acceptableCharsets(): array
	{
		if(!isset($this->acceptableCharsets))
		{
			$this->acceptableCharsets = $this->parseAcceptHeader($this->get('accept-charset'));
		}

		return $this->acceptableCharsets;
	}

	/**
	 * Returns an array of acceptable content types in descending order of preference.
	 *
	 * @access public
	 * @return array
	 */
	public function acceptableEncodings(): array
	{
		if(!isset($this->acceptableEncodings))
		{
			$this->acceptableEncodings = $this->parseAcceptHeader($this->get('accept-encoding'));
		}

		return $this->acceptableEncodings;
	}
}
