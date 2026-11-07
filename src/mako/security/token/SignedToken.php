<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\token;

use InvalidArgumentException;
use JsonException;
use mako\chrono\Clock;
use mako\security\signer\Signer;
use mako\security\token\exceptions\TokenException;
use Psr\Clock\ClockInterface;

use function base64_decode;
use function base64_encode;
use function filter_var;
use function is_int;
use function is_string;
use function json_encode;
use function ksort;
use function preg_match;
use function rtrim;
use function strlen;
use function strtr;
use function substr;

/**
 * Creates and validates time-limited signed tokens.
 */
class SignedToken
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected readonly Signer $signer,
		protected readonly ClockInterface $clock = new Clock
	) {
	}

	/**
	 * Builds the signed payload.
	 *
	 * Metadata values are type-sensitive and must match during validation.
	 *
	 * @param array<string, int|string> $meta
	 */
	protected function buildPayload(string $identifier, int $expires, array $meta): string
	{
		foreach ($meta as $key => $value) {
			if (!is_string($key) || !(is_string($value) || is_int($value))) {
				throw new InvalidArgumentException('Invalid token metadata.');
			}
		}

		ksort($meta, SORT_STRING);

		return json_encode([
			'type'       => 'signed-token',
			'identifier' => $identifier,
			'expires'    => $expires,
			'meta'       => (object) $meta,
		], JSON_THROW_ON_ERROR);
	}

	/**
	 * Base64URL encodes a string.
	 */
	protected function encode(string $string): string
	{
		return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
	}

	/**
	 * Creates a signed token.
	 *
	 * @param array<string, int|string> $meta
	 */
	public function create(string $identifier, int $ttl, array $meta = []): string
	{
		$now = $this->clock->now()->getTimestamp();

		if ($ttl <= 0 || $ttl > PHP_INT_MAX - $now) {
			throw new InvalidArgumentException('Invalid token TTL.');
		}

		$expires = $now + $ttl;

		$payload = $this->buildPayload($identifier, $expires, $meta);

		// Sign the payload and extract the hexadecimal HMAC

		$signed = $this->signer->sign($payload);

		$signature = substr($signed, 0, strlen($signed) - strlen($payload));

		// Encode only the expiration and signature

		return $this->encode("{$expires}:{$signature}");
	}

	/**
	 * Base64URL decodes a string.
	 */
	protected function decode(string $string): false|string
	{
		// Ensure that the string only contains valid Base64URL characters and has a valid length

		if (
			preg_match('/\A[A-Za-z0-9_-]+\z/', $string) !== 1 ||
			strlen($string) % 4 === 1
		) {
			return false;
		}

		$decoded = base64_decode(strtr($string, '-_', '+/'), true);

		// Ensure that the decoded string re-encodes to the original Base64URL string

		return $decoded !== false && $this->encode($decoded) === $string
			? $decoded
			: false;
	}

	/**
	 * Validates a signed token.
	 *
	 * @param array<string, int|string> $meta
	 */
	public function validate(string $token, string $identifier, array $meta = []): bool
	{
		if ($token === '' || strlen($token) > 1024) {
			return false;
		}

		$decoded = $this->decode($token);

		// Ensure that the decoded token contains a valid expiration and signature

		if (
			$decoded === false ||
			!preg_match('/\A([1-9][0-9]*):([a-f0-9]{64})\z/', $decoded, $matches)
		) {
			return false;
		}

		$expires = filter_var($matches[1], FILTER_VALIDATE_INT);

		if ($expires === false || $expires <= $this->clock->now()->getTimestamp()) {
			return false;
		}

		try {
			$payload = $this->buildPayload($identifier, $expires, $meta);
		}
		catch (JsonException) {
			return false;
		}

		// Reconstruct the signed string for validation

		return $this->signer->validate($matches[2] . $payload) === $payload;
	}

	/**
	 * Validates a signed token or throws an exception.
	 *
	 * @param array<string, int|string> $meta
	 */
	public function validateOrThrow(string $token, string $identifier, array $meta = []): void
	{
		if (!$this->validate($token, $identifier, $meta)) {
			throw new TokenException('The token is invalid or has expired.');
		}
	}
}
