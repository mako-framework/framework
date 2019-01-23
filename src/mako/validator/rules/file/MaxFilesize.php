<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\WithParametersTrait;
use mako\validator\rules\WithParametersInterface;
use RuntimeException;

use function is_numeric;
use function sprintf;
use function substr;
use function vsprintf;

/**
 * Max filesize rule.
 *
 * @author Frederic G. Østby
 */
class MaxFilesize extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['maxSize'];

	/**
	 * Convert human friendly size to bytes.
	 *
	 * @param  int|string        $size Size
	 * @throws \RuntimeException
	 * @return int|float
	 */
	protected function convertToBytes($size)
	{
		if(is_numeric($unit = substr($size, -3)) === false)
		{
			$size = substr($size, 0, -3);

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
					throw new RuntimeException(vsprintf('Invalid unit type [ %s ].', [$unit]));
			}
		}

		return (int) $size;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		$maxSize = $this->convertToBytes($this->getParameter('maxSize'));

		return $value->getSize() <= $maxSize;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be less than %2$s in size.', $field, $this->parameters['maxSize']);
	}
}
