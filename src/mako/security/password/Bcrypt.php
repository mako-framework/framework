<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use Override;

use function max;
use function min;

/**
 * Bcrypt hasher.
 */
class Bcrypt extends Hasher
{
	/**
	 * Minimum supported cost.
	 */
	protected const int MIN_COST = 4;

	/**
	 * Maximum supported cost.
	 */
	protected const int MAX_COST = 31;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function normalizeOptions(array $options): array
	{
		$options += [
			'cost' => PASSWORD_BCRYPT_DEFAULT_COST,
		];

		$options['cost'] = max(min($options['cost'], static::MAX_COST), static::MIN_COST);

		return $options;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getAlgorithm(): string
	{
		return PASSWORD_BCRYPT;
	}
}
