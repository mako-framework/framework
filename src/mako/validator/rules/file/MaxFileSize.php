<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\exceptions\ValidatorException;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

use function is_numeric;
use function sprintf;
use function substr;
use function vsprintf;

/**
 * Max file size rule.
 */
class MaxFileSize extends Rule implements RuleInterface
{
	/**
	 * Max size.
	 *
	 * @var int|string
	 */
	protected $maxSize;

	/**
	 * Constructor.
	 *
	 * @param int|string $maxSize Max size
	 */
	public function __construct($maxSize)
	{
		$this->maxSize = $maxSize;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['maxSize'];

	/**
	 * Convert human friendly size to bytes.
	 *
	 * @param  int|string $size Size
	 * @return float|int
	 */
	protected function convertToBytes($size)
	{
		if(is_numeric($unit = substr($size, -3)) === false)
		{
			$size = (int) substr($size, 0, -3);

			switch($unit)
			{
				case 'KiB':
					return $size * 1024;
				case 'MiB':
					return $size * (1024 ** 2);
				case 'GiB':
					return $size * (1024 ** 3);
				case 'TiB':
					return $size * (1024 ** 4);
				case 'PiB':
					return $size * (1024 ** 5);
				case 'EiB':
					return $size * (1024 ** 6);
				case 'ZiB':
					return $size * (1024 ** 7);
				case 'YiB':
					return $size * (1024 ** 8);
				default:
					throw new ValidatorException(vsprintf('Invalid unit type [ %s ].', [$unit]));
			}
		}

		return (int) $size;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, string $field, array $input): bool
	{
		return $value->getSize() <= $this->convertToBytes($this->maxSize);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be less than %2$s in size.', $field, $this->maxSize);
	}
}
