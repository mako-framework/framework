<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use ErrorException;
use Override;
use Throwable;

use function in_array;

/**
 * Deprections hint.
 */
class Deprecations implements HintInterface
{
	/**
	 * Deprecation codes.
	 */
	protected const array CODES = [
		E_DEPRECATED,
		E_USER_DEPRECATED,
	];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function canProvideHint(Throwable $exception): bool
    {
		return $exception instanceof ErrorException && in_array($exception->getCode(), static::CODES);
    }

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getHint(Throwable $exception): ?string
    {
		$type = match ($exception->getCode()) {
			E_DEPRECATED      => 'E_DEPRECATED',
			E_USER_DEPRECATED => 'E_USER_DEPRECATED',
			default           => null,
		};

		if ($type === null) {
			return null;
		}

		return <<<HINT
		Update your code to resolve this deprecation. If you intentionally want to ignore this type of deprecation, exclude it from error_reporting. For example:

		error_reporting(E_ALL & ~{$type});

		Alternatively, you may be able to suppress individual deprecations with the @ operator.
		HINT;
    }
}
