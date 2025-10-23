<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use mako\common\traits\SuggestionTrait;
use Override;
use Throwable;

use function array_merge;
use function end;
use function explode;
use function get_defined_functions;
use function preg_match;
use function str_contains;
use function str_starts_with;

/**
 * Undefined function hint.
 */
class UndefinedFunction implements HintInterface
{
	use SuggestionTrait;

	/**
	 * Regex that matches the function name in the error message.
	 */
	protected const string REGEX = '/^Call to undefined function (.*)\(\)/u';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function canProvideHint(Throwable $exception): bool
	{
		return str_starts_with($exception->getMessage(), 'Call to undefined function');
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHint(Throwable $exception): ?string
	{
		if (preg_match(static::REGEX, $exception->getMessage(), $matches) !== 1) {
			return null;
		}

		$function = $matches[1];

		$defined = get_defined_functions();

    	$functions = array_merge($defined['internal'], $defined['user']);

    	$suggestion = $this->suggest($function, $functions);

		if ($suggestion === null && str_contains($function, '\\')) {
			$function = explode('\\', $function);
			$function = end($function);

			$suggestion = $this->suggest($function, $functions); // Try again without namespace
		}

		return $suggestion === null ? null : "Did you mean to call the {$suggestion}() function?";
	}
}
