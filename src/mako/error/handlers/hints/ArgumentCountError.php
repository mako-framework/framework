<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use ArgumentCountError as ArgumentCountErrorException;
use Override;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;

use function explode;
use function implode;
use function preg_match;
use function str_contains;
use function var_export;

/**
 * Argument count error hint.
 */
class ArgumentCountError implements HintInterface
{
	/**
	 * Regex that matches the function or method name in the error message.
	 */
	protected const string REGEX = '/\b([\p{L}\p{N}_\\\]+(?:::[\p{L}\p{N}_]+)?)(?=\(\))/u';

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function canProvideHint(Throwable $exception): bool
    {
		return $exception instanceof ArgumentCountErrorException;
    }

	/**
	 * @param array<\ReflectionParameter> $reflectionParameters
	 */
	protected function getParameters(array $reflectionParameters): string
	{
		$parameters = [];

		foreach ($reflectionParameters as $reflectionParameter) {
			$parameter = '';

			if ($reflectionParameter->hasType()) {
				$parameter .= "{$reflectionParameter->getType()} ";
			}

			if ($reflectionParameter->isPassedByReference()) {
				$parameter .= '&';
			}

			$parameter .= "\${$reflectionParameter->getName()}";

			if ($reflectionParameter->isDefaultValueAvailable()) {
				if ($reflectionParameter->isDefaultValueConstant()) {
					$parameter .= " = {$reflectionParameter->getDefaultValueConstantName()}";
				}
				else {
					$parameter .= ' = ' . var_export($reflectionParameter->getDefaultValue(), true);
				}
			}

			$parameters[] = $parameter;
		}

		return implode(', ', $parameters);
	}

	/**
	 * Returns the return type.
	 */
	protected function getReturnType(ReflectionFunction|ReflectionMethod $reflection): ?string
	{
		if ($reflection->hasReturnType()) {
			return ": {$reflection->getReturnType()}";
		}

		return null;
	}

	/**
	 * Returns the method signature.
	 */
	protected function getMethodSignature(string $method): array
	{
		[$class, $method] = explode('::', $method, 2);

		$reflectionMethod = (new ReflectionClass($class))->getMethod($method);

		return [
			$this->getParameters($reflectionMethod->getParameters()),
			$this->getReturnType($reflectionMethod),
		];
	}

	/**
	 * Returns the function signature.
	 */
	protected function getFunctionSignature(string $function): array
	{
		$reflectionFunction = new ReflectionFunction($function);

		return [
			$this->getParameters($reflectionFunction->getParameters()),
			$this->getReturnType($reflectionFunction),
		];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
    public function getHint(Throwable $exception): ?string
    {
		$message = $exception->getMessage();

		if (
			str_contains($message, '{closure:')
			|| str_contains($message, 'class@anonymous')
			|| preg_match(static::REGEX, $message, $matches) !== 1
		) {
			return null;
		}

		[$function] = $matches;

		if (str_contains($function, '::')) {
			$type = 'method';
			[$parameters, $returnType] = $this->getMethodSignature($function);
		}
		else {
			$type = 'function';
			[$parameters, $returnType] = $this->getFunctionSignature($function);
		}

		return <<<HINT
		The {$type} signature is:

		{$function}({$parameters}){$returnType}
		HINT;
    }
}
