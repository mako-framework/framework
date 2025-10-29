<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints\traits;

use mako\error\handlers\hints\ArgumentCountError;
use mako\error\handlers\hints\ContainerException;
use mako\error\handlers\hints\UndefinedConstant;
use mako\error\handlers\hints\UndefinedFunction;
use mako\error\handlers\hints\UndefinedMethod;
use mako\error\handlers\hints\UndefinedProperty;
use Throwable;

/**
 * Hint trait.
 */
trait HintTrait
{
	/**
	 * Returns a hint if possible.
	 */
	protected function getHint(Throwable $exception): ?string
	{
		/** @var array<class-string<\mako\error\handlers\hints\HintInterface>> $hints */
		$hints = [
			ArgumentCountError::class,
			ContainerException::class,
			UndefinedConstant::class,
			UndefinedFunction::class,
			UndefinedMethod::class,
			UndefinedProperty::class,
		];

		try {
			foreach ($hints as $hintClass) {
				$hint = new $hintClass;

				if ($hint->canProvideHint($exception)) {
					return $hint->getHint($exception);
				}
			}
		}
		catch (Throwable) {
			// Do nothing if a hint fails
		}

		return null;
	}
}
