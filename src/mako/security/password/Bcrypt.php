<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\security\password;

use function max;
use function min;

/**
 * Bcrypt hasher.
 *
 * @author Frederic G. Østby
 */
class Bcrypt extends Hasher
{
	/**
	 * {@inheritDoc}
	 */
	protected function normalizeOptions(array $options): array
	{
		$options +=
		[
			'cost' => PASSWORD_BCRYPT_DEFAULT_COST,
		];

		$options['cost'] = max(min($options['cost'], 31), 4);

		return $options;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getAlgorithm()
	{
		return PASSWORD_BCRYPT;
	}
}
