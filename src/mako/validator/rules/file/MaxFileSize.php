<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\exceptions\ValidatorException;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use Override;

use function is_numeric;
use function sprintf;
use function strtolower;
use function substr;

/**
 * Max file size rule.
 */
class MaxFileSize extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int|string $maxSize
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['maxSize'];

	/**
	 * Convert human friendly size to bytes.
	 */
	protected function convertToBytes(int|string $size): float|int
	{
		if (is_numeric($unit = substr($size, -3)) === false) {
			$size = (int) substr($size, 0, -3);

			return match (strtolower($unit)) {
				'kib'   => $size * 1024,
				'mib'   => $size * (1024 ** 2),
				'gib'   => $size * (1024 ** 3),
				'tib'   => $size * (1024 ** 4),
				'pib'   => $size * (1024 ** 5),
				'eib'   => $size * (1024 ** 6),
				'zib'   => $size * (1024 ** 7),
				'yib'   => $size * (1024 ** 8),
				default => throw new ValidatorException(sprintf('Invalid unit type [ %s ].', $unit)),
			};
		}

		return (int) $size;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value->getSize() <= $this->convertToBytes($this->maxSize);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be less than %2$s in size.', $field, $this->maxSize);
	}
}
