<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\request;

use mako\http\exceptions\BadRequestException;

use function is_array;
use function json_decode;
use function json_last_error;
use function parse_str;
use function str_contains;

/**
 * Body.
 */
class Body extends Parameters
{
	/**
	 * Maximum JSON nesting depth depth.
	 */
	protected static int $depth = 512;

	/**
	 * JSON flags.
	 */
	protected static int $flags = 0;

	/**
	 * Constructor.
	 */
	public function __construct(string $rawBody, string $contentType)
	{
		parent::__construct($this->parseBody($rawBody, $contentType));
	}

	/**
	 * Set the maximum nesting depth of the JSON structure being decoded.
	 */
	public static function setJsonMaxDepth(int $depth): void
	{
		static::$depth = $depth;
	}

	/**
	 * Set the JSON decode flags.
	 */
	public static function setJsonFlags(int $flags): void
	{
		static::$flags = $flags;
	}

	/**
	 * Converts the request body into an associative array.
	 */
	protected function parseBody(string $rawBody, string $contentType): array
	{
		if ($contentType === 'application/x-www-form-urlencoded') {
			$parsed = [];

			parse_str($rawBody, $parsed);

			return $parsed;
		}

		if ($contentType === 'application/json' || $contentType === 'text/json' || str_contains($contentType, '+json')) {
			$parsed = json_decode($rawBody, true, static::$depth, static::$flags);

			if (json_last_error() !== JSON_ERROR_NONE || is_array($parsed) === false) {
				throw new BadRequestException;
			}

			return $parsed;
		}

		return [];
	}
}
