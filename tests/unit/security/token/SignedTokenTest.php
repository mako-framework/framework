<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\token;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use mako\security\signer\Signer;
use mako\security\token\exceptions\TokenException;
use mako\security\token\SignedToken;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Psr\Clock\ClockInterface;
use stdClass;

#[Group('unit')]
class SignedTokenTest extends TestCase
{
	protected const string SECRET = '0123456789abcdef0123456789abcdef';

	protected const int NOW = 1700000000;

	/**
	 * Base64URL encodes a string.
	 */
	protected static function encode(string $string): string
	{
		return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
	}

	/**
	 * Base64URL decodes a string.
	 */
	protected static function decode(string $string): string
	{
		return base64_decode(strtr($string, '-_', '+/'));
	}

	/**
	 * Builds a token the same way SignedToken does.
	 */
	protected static function buildToken(int $expires, string $identifier, array $meta = [], string $secret = self::SECRET): string
	{
		ksort($meta, SORT_STRING);

		$payload = json_encode([
			'type'       => 'signed-token',
			'identifier' => $identifier,
			'expires'    => $expires,
			'meta'       => (object) $meta,
		]);

		return static::encode("{$expires}:" . hash_hmac('sha256', $payload, $secret));
	}

	/**
	 *
	 */
	protected function getClock(int $timestamp): ClockInterface
	{
		$clock = Mockery::mock(ClockInterface::class);

		$clock->shouldReceive('now')->andReturn(new DateTimeImmutable("@{$timestamp}"));

		return $clock;
	}

	/**
	 *
	 */
	protected function getSignedToken(int $timestamp = self::NOW, string $secret = self::SECRET): SignedToken
	{
		return new SignedToken(new Signer($secret), $this->getClock($timestamp));
	}

	/**
	 * Returns [ttl].
	 */
	public static function invalidTtlProvider(): array
	{
		return [
			'zero'     => [0],
			'negative' => [-1],
			'overflow' => [PHP_INT_MAX],
		];
	}

	/**
	 * Returns [meta].
	 */
	public static function invalidMetaProvider(): array
	{
		return [
			'float value'  => [['foo' => 1.5], 'Invalid token metadata value for key [ foo ].'],
			'bool value'   => [['foo' => true], 'Invalid token metadata value for key [ foo ].'],
			'null value'   => [['foo' => null], 'Invalid token metadata value for key [ foo ].'],
			'array value'  => [['foo' => ['bar']], 'Invalid token metadata value for key [ foo ].'],
			'object value' => [['foo' => new stdClass], 'Invalid token metadata value for key [ foo ].'],
			'integer key'  => [[0 => 'foo'], 'Token metadata keys must be strings.'],
		];
	}

	/**
	 * Returns [meta used when creating, meta used when validating].
	 */
	public static function mismatchedMetaProvider(): array
	{
		return [
			'different value' => [['foo' => 'bar'], ['foo' => 'baz']],
			'different key'   => [['foo' => 'bar'], ['bar' => 'bar']],
			'missing meta'    => [['foo' => 'bar'], []],
			'extra meta'      => [[], ['foo' => 'bar']],
			'type mismatch'   => [['foo' => 1], ['foo' => '1']],
		];
	}

	/**
	 * Returns [token].
	 */
	public static function malformedTokenProvider(): array
	{
		$signature = str_repeat('a', 64);

		return [
			'invalid character +'    => ['foo+bar'],
			'invalid character /'    => ['foo/bar'],
			'padding'                => ['foo='],
			'invalid length'         => ['abcde'],
			'non-canonical encoding' => ['YR'],
			'missing separator'      => [static::encode('foobar')],
			'empty expiration'       => [static::encode(":{$signature}")],
			'leading zero'           => [static::encode("0123:{$signature}")],
			'negative expiration'    => [static::encode("-123:{$signature}")],
			'overflowing expiration' => [static::encode(PHP_INT_MAX . "0:{$signature}")],
			'uppercase signature'    => [static::encode('123:' . str_repeat('A', 64))],
			'short signature'        => [static::encode('123:' . str_repeat('a', 63))],
			'long signature'         => [static::encode('123:' . str_repeat('a', 65))],
			'trailing newline'       => [static::encode("123:{$signature}\n")],
		];
	}

	/**
	 *
	 */
	public function testCreate(): void
	{
		$token = $this->getSignedToken()->create('foobar', 3600);

		$this->assertSame(static::buildToken(static::NOW + 3600, 'foobar'), $token);
	}

	/**
	 *
	 */
	public function testCreateWithMeta(): void
	{
		$token = $this->getSignedToken()->create('foobar', 3600, ['foo' => 'bar', 'bar' => 1]);

		$this->assertSame(static::buildToken(static::NOW + 3600, 'foobar', ['foo' => 'bar', 'bar' => 1]), $token);
	}

	/**
	 *
	 */
	public function testCreateWithMetaIsOrderInsensitive(): void
	{
		$signedToken = $this->getSignedToken();

		$this->assertSame(
			$signedToken->create('foobar', 3600, ['foo' => 'bar', 'bar' => 1]),
			$signedToken->create('foobar', 3600, ['bar' => 1, 'foo' => 'bar'])
		);
	}

	/**
	 *
	 */
	public function testCreateIsBoundToInput(): void
	{
		$signedToken = $this->getSignedToken();

		$token = $signedToken->create('foobar', 3600);

		$this->assertNotSame($token, $signedToken->create('barfoo', 3600));
		$this->assertNotSame($token, $signedToken->create('foobar', 7200));
		$this->assertNotSame($token, $signedToken->create('foobar', 3600, ['foo' => 'bar']));
		$this->assertNotSame($token, $this->getSignedToken(static::NOW, str_repeat('x', 32))->create('foobar', 3600));
	}

	/**
	 *
	 */
	#[DataProvider('invalidTtlProvider')]
	public function testCreateWithInvalidTtl(int $ttl): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Invalid token TTL.');

		$this->getSignedToken()->create('foobar', $ttl);
	}

	/**
	 *
	 */
	#[DataProvider('invalidMetaProvider')]
	public function testCreateWithInvalidMeta(array $meta, string $message): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs($message);

		$this->getSignedToken()->create('foobar', 3600, $meta);
	}

	/**
	 *
	 */
	public function testValidate(): void
	{
		$signedToken = $this->getSignedToken();

		$this->assertTrue($signedToken->validate($signedToken->create('foobar', 3600), 'foobar'));
	}

	/**
	 *
	 */
	public function testValidateWithMeta(): void
	{
		$signedToken = $this->getSignedToken();

		$token = $signedToken->create('foobar', 3600, ['foo' => 'bar', 'bar' => 1]);

		$this->assertTrue($signedToken->validate($token, 'foobar', ['foo' => 'bar', 'bar' => 1]));
		$this->assertTrue($signedToken->validate($token, 'foobar', ['bar' => 1, 'foo' => 'bar']));
	}

	/**
	 *
	 */
	public function testValidateWithWrongIdentifier(): void
	{
		$signedToken = $this->getSignedToken();

		$this->assertFalse($signedToken->validate($signedToken->create('foobar', 3600), 'barfoo'));
	}

	/**
	 *
	 */
	#[DataProvider('mismatchedMetaProvider')]
	public function testValidateWithMismatchedMeta(array $created, array $validated): void
	{
		$signedToken = $this->getSignedToken();

		$this->assertFalse($signedToken->validate($signedToken->create('foobar', 3600, $created), 'foobar', $validated));
	}

	/**
	 *
	 */
	#[DataProvider('invalidMetaProvider')]
	public function testValidateWithInvalidMeta(array $meta, string $message): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs($message);

		$signedToken = $this->getSignedToken();

		$signedToken->validate($signedToken->create('foobar', 3600), 'foobar', $meta);
	}

	/**
	 *
	 */
	public function testValidateWithDifferentSecret(): void
	{
		$token = $this->getSignedToken()->create('foobar', 3600);

		$this->assertFalse($this->getSignedToken(static::NOW, str_repeat('x', 32))->validate($token, 'foobar'));
	}

	/**
	 *
	 */
	public function testValidateExpiration(): void
	{
		$token = $this->getSignedToken()->create('foobar', 60);

		$this->assertTrue($this->getSignedToken(static::NOW + 59)->validate($token, 'foobar'));
		$this->assertFalse($this->getSignedToken(static::NOW + 60)->validate($token, 'foobar'));
		$this->assertFalse($this->getSignedToken(static::NOW + 61)->validate($token, 'foobar'));
		$this->assertTrue($this->getSignedToken(static::NOW - 60)->validate($token, 'foobar'));
	}

	/**
	 *
	 */
	public function testCreateWithMaximumTtl(): void
	{
		$ttl = PHP_INT_MAX - static::NOW;

		$token = $this->getSignedToken()->create('foobar', $ttl);

		$this->assertSame(
			static::buildToken(PHP_INT_MAX, 'foobar'),
			$token
		);
	}

	/**
	 *
	 */
	public function testValidateWithEmptyToken(): void
	{
		$this->assertFalse($this->getSignedToken()->validate('', 'foobar'));
	}

	/**
	 *
	 */
	public function testValidateWithTooLongToken(): void
	{
		$this->assertFalse($this->getSignedToken()->validate(str_repeat('a', 1025), 'foobar'));
	}

	/**
	 *
	 */
	#[DataProvider('malformedTokenProvider')]
	public function testValidateWithMalformedToken(string $token): void
	{
		$this->assertFalse($this->getSignedToken()->validate($token, 'foobar'));
	}

	/**
	 *
	 */
	public function testValidateWithTamperedSignature(): void
	{
		$signedToken = $this->getSignedToken();

		[$expires, $signature] = explode(':', static::decode($signedToken->create('foobar', 3600)));

		$signature[0] = $signature[0] === 'a' ? 'b' : 'a';

		$this->assertFalse($signedToken->validate(static::encode("{$expires}:{$signature}"), 'foobar'));
	}

	/**
	 *
	 */
	public function testValidateWithTamperedExpiration(): void
	{
		[, $signature] = explode(':', static::decode($this->getSignedToken()->create('foobar', 60)));

		$token = static::encode((static::NOW + 7200) . ":{$signature}");

		$this->assertFalse($this->getSignedToken(static::NOW + 3600)->validate($token, 'foobar'));
	}

	/**
	 *
	 */
	public function testValidateOrThrow(): void
	{
		$this->expectNotToPerformAssertions();

		$signedToken = $this->getSignedToken();

		$signedToken->validateOrThrow($signedToken->create('foobar', 3600), 'foobar');
	}

	/**
	 *
	 */
	public function testValidateOrThrowWithInvalidToken(): void
	{
		$this->expectException(TokenException::class);
		$this->expectExceptionMessageIs('The token is invalid or has expired.');

		$this->getSignedToken()->validateOrThrow('foobar', 'foobar');
	}

	/**
	 *
	 */
	public function testValidateOrThrowWithExpiredToken(): void
	{
		$this->expectException(TokenException::class);
		$this->expectExceptionMessageIs('The token is invalid or has expired.');

		$token = $this->getSignedToken()->create('foobar', 60);

		$this->getSignedToken(static::NOW + 60)->validateOrThrow($token, 'foobar');
	}

	/**
	 *
	 */
	public function testCreateWithInvalidUtf8(): void
	{
		$this->expectException(JsonException::class);

		$this->getSignedToken()->create("\xB1", 3600);
	}

	/**
	 *
	 */
	public function testCreateWithInvalidUtf8MetaKey(): void
	{
		$this->expectException(JsonException::class);

		$this->getSignedToken()->create('foobar', 3600, [
			"\xB1" => 'value',
		]);
	}

	/**
	 *
	 */
	public function testValidateWithInvalidUtf8(): void
	{
		$signedToken = $this->getSignedToken();

		$token = $signedToken->create('foobar', 3600);

		$this->assertFalse($signedToken->validate($token, "\xB1"));
	}
}
