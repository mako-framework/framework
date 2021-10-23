<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

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
	 *
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param array $options algorithm options
	 */
	public function __construct(array $options = [])
	{
		$this->options = $this->normalizeOptions($options);
	}

	/**
	 * Normalizes the algorithm options.
	 *
	 * @param  array $options Algorithm options
	 * @return array
	 */
	protected function normalizeOptions(array $options): array
	{
		return $options;
	}

	/**
	 * Returns the algorithm type.
	 *
	 * @return string|null
	 */
	abstract protected function getAlgorithm(): ?string;

	/**
	 * {@inheritDoc}
	 */
	public function create(string $password): string
	{
		$hash = password_hash($password, $this->getAlgorithm(), $this->options);

		if($hash === false)
		{
			throw new HasherException('Failed to generate hash.');
		}

		return $hash;
	}

	/**
	 * {@inheritDoc}
	 */
	public function verify(string $password, string $hash): bool
	{
		return password_verify($password, $hash);
	}

	/**
	 * {@inheritDoc}
	 */
	public function needsRehash(string $hash): bool
	{
		return password_needs_rehash($hash, $this->getAlgorithm(), $this->options);
	}
}
