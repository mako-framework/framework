<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\error\handlers\hints;

use mako\syringe\exceptions\ContainerException as MakoContainerException;
use mako\syringe\exceptions\UnableToInstantiateException;
use mako\syringe\exceptions\UnableToResolveParameterException;
use Override;
use Throwable;

use function preg_match;
use function str_starts_with;

/**
 * Container exception hint.
 */
class ContainerException implements HintInterface
{
	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function canProvideHint(Throwable $exception): bool
	{
		return $exception instanceof MakoContainerException;
	}

	/**
	 * Returns a suggestion for which service that needs to be enabled.
	 */
	protected function matchService(string $class): ?string
	{
		return match (true) {
			str_starts_with($class, 'mako\cache') => 'CacheService',
			str_starts_with($class, 'mako\database') => 'DatabaseService',
			str_starts_with($class, 'mako\gatekeeper') => 'GatekeeperService',
			str_starts_with($class, 'mako\i18n') => 'I18nService',
			str_starts_with($class, 'mako\redis') => 'RedisService',
			str_starts_with($class, 'mako\throttle') => 'RateLimiterService',
			default => null,
		};
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getHint(Throwable $exception): ?string
	{
		if ($exception instanceof UnableToInstantiateException || $exception instanceof UnableToResolveParameterException) {
			$class = null;

			if (preg_match('/Unable to create a \[ (.*) \] instance\./', $exception->getMessage(), $matches) === 1) {
				[, $class] = $matches;
			}
			elseif (preg_match('/Unable to resolve the \[ .* \] parameter of \[ (.*) \]\./', $exception->getMessage(), $matches) === 1) {
				[, $class] = $matches;
			}

			if ($class !== null) {
				$service = $this->matchService($class);

				if ($service !== null) {
					return "Have you forgotten to enable the {$service} service?";
				}
			}

			return 'Have you forgotten to enable a service or to register a dependency in the container?';
		}

		return null;
	}
}
