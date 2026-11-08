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
use SensitiveParameter;

use function base64_decode;
use function base64_encode;
use function filter_var;
use function is_int;
use function is_string;
use function json_encode;
use function ksort;
use function preg_match;
use function rtrim;
use function sprintf;
use function strlen;
use function strtr;
use function substr;

/**
 * Creates and validates time-limited signed tokens.
 */
final class SignedToken
{
	/**
	 * Maximum token length.
	 */
	private const int MAX_TOKEN_LENGTH = 1024;

	/**
	 * Constructor.
	 */
	public function __construct(
		private readonly Signer $signer,
		private readonly ClockInterface $clock = new Clock
	) {
	}

	/**
	 * Calculates the expiration timestamp.
	 */
	private function getExpiration(int $ttl): int
	{
		$now = $this->clock->now()->getTimestamp();

		if ($ttl <= 0 || $ttl > PHP_INT_MAX - $now) {
			throw new InvalidArgumentException('Invalid token TTL.');
		}

		$expires = $now + $ttl;

		return $expires;
	}

	/**
	 * Builds the signed payload.
	 *
	 * Metadata values are type-sensitive and must match during validation.
	 *
	 * @param array<string, int|string> $meta
	 */
	private function buildPayload(
		#[SensitiveParameter] string $identifier,
		int $expires,
		#[SensitiveParameter] array $meta
	): string {
		foreach ($meta as $key => $value) {
			if (!is_string($key)) {
				throw new InvalidArgumentException('Token metadata keys must be strings.');
			}

			if (!(is_string($value) || is_int($value))) {
				throw new InvalidArgumentException(sprintf('Invalid token metadata value for key [ %s ].', $key));
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
	private function encode(#[SensitiveParameter] string $string): string
	{
		return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
	}

	/**
	 * Creates a signed token.
	 *
	 * @param array<string, int|string> $meta
	 */
	public function create(
		#[SensitiveParameter] string $identifier,
		int $ttl,
		#[SensitiveParameter] array $meta = []
	): string {
		$expires = $this->getExpiration($ttl);

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
	private function decode(#[SensitiveParameter] string $string): false|string
	{
		// Ensure that the string only contains valid Base64URL characters and has a valid length

		if (preg_match('/\A[A-Za-z0-9_-]+\z/', $string) !== 1 || strlen($string) % 4 === 1) {
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
	public function validate(
		#[SensitiveParameter] string $token,
		#[SensitiveParameter] string $identifier,
		#[SensitiveParameter] array $meta = []
	): bool {
		if ($token === '' || strlen($token) > self::MAX_TOKEN_LENGTH) {
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
	public function validateOrThrow(
		#[SensitiveParameter] string $token,
		#[SensitiveParameter] string $identifier,
		#[SensitiveParameter] array $meta = []
	): void {
		if (!$this->validate($token, $identifier, $meta)) {
			throw new TokenException('The token is invalid or has expired.');
		}
	}
}
