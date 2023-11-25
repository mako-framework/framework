<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use mako\security\password\exceptions\HasherException;
use SensitiveParameter;
use Throwable;

use function password_hash;
use function password_needs_rehash;
use function password_verify;

/**
 * Base hasher.
 */
abstract class Hasher implements HasherInterface
{
	/**
	 * Algorithm options.
	 */
	protected array $options;

	/**
	 * Constructor.
	 */
	public function __construct(array $options = [])
	{
		$this->options = $this->normalizeOptions($options);
	}

	/**
	 * Normalizes the algorithm options.
	 */
	protected function normalizeOptions(array $options): array
	{
		return $options;
	}

	/**
	 * Returns the algorithm type.
	 */
	abstract protected function getAlgorithm(): ?string;

	/**
	 * {@inheritDoc}
	 */
	public function create(#[SensitiveParameter] string $password): string
	{
		try {
			return password_hash($password, $this->getAlgorithm(), $this->options);
		}
		catch (Throwable $e) {
			throw new HasherException('Failed to generate hash.', previous: $e);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function verify(#[SensitiveParameter] string $password, #[SensitiveParameter] string $hash): bool
	{
		return password_verify($password, $hash);
	}

	/**
	 * {@inheritDoc}
	 */
	public function needsRehash(#[SensitiveParameter] string $hash): bool
	{
		return password_needs_rehash($hash, $this->getAlgorithm(), $this->options);
	}
}
