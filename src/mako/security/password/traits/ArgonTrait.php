<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password\traits;

use mako\security\password\Hasher;
use Override;

use function max;

/**
 * Argon trait.
 *
 * @phpstan-require-extends Hasher
 */
trait ArgonTrait
{
	/**
	 * Minimum supported time cost.
	 */
	protected const int MIN_TIME_COST = 1;

	/**
	 * Minimum supported threads.
	 */
	protected const int MIN_THREADS = 1;

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function normalizeOptions(array $options): array
	{
		$options += [
			'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
			'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
			'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
		];

		// Memory cost is the primary defense against GPU/ASIC cracking, so we
		// don't allow it to be set below the secure default.

		$options['memory_cost'] = max($options['memory_cost'], PASSWORD_ARGON2_DEFAULT_MEMORY_COST);

		// Time cost and threads have a smaller security impact and can be
		// lowered (e.g. for faster tests), as long as they stay within valid bounds.

		$options['time_cost'] = max($options['time_cost'], static::MIN_TIME_COST);
		$options['threads']   = max($options['threads'], static::MIN_THREADS);

		return $options;
	}
}
