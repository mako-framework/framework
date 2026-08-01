<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe\traits;

use mako\application\Application;
use mako\bus\command\CommandBusInterface;
use mako\bus\event\EventBusInterface;
use mako\bus\query\QueryBusInterface;
use mako\cache\CacheManager;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\config\Config;
use mako\database\ConnectionManager as DatabaseConnectionManager;
use mako\error\ErrorHandler;
use mako\file\FileSystem;
use mako\gatekeeper\authorization\AuthorizerInterface;
use mako\gatekeeper\Gatekeeper;
use mako\http\Request;
use mako\http\Response;
use mako\http\routing\Routes;
use mako\http\routing\URLBuilder;
use mako\i18n\I18n;
use mako\logger\Logger;
use mako\pagination\PaginationFactoryInterface;
use mako\redis\ConnectionManager as RedisConnectionManager;
use mako\security\crypto\CryptoManager;
use mako\security\signer\Signer;
use mako\session\Session;
use mako\syringe\Container;
use mako\syringe\exceptions\ContainerException;
use mako\throttle\RateLimiterInterface;
use mako\utility\Humanizer;
use mako\validator\ValidatorFactory;
use mako\view\ViewFactory;

use function sprintf;

/**
 * Container aware trait.
 *
 * @property Application                $app
 * @property CommandBusInterface        $commandBus
 * @property EventBusInterface          $eventBus
 * @property QueryBusInterface          $queryBus
 * @property CacheManager               $cache
 * @property Input                      $input
 * @property Output                     $output
 * @property Config                     $config
 * @property DatabaseConnectionManager  $database
 * @property ErrorHandler               $errorHandler
 * @property FileSystem                 $fileSystem
 * @property AuthorizerInterface        $authorizer
 * @property Gatekeeper                 $gatekeeper
 * @property Request                    $request
 * @property Response                   $response
 * @property Routes                     $routes
 * @property URLBuilder                 $urlBuilder
 * @property I18n                       $i18n
 * @property Logger                     $logger
 * @property PaginationFactoryInterface $pagination
 * @property RedisConnectionManager     $redis
 * @property CryptoManager              $crypto
 * @property Signer                     $signer
 * @property Session                    $session
 * @property RateLimiterInterface       $rateLimiter
 * @property Humanizer                  $humanizer
 * @property ValidatorFactory           $validator
 * @property ViewFactory                $view
 */
trait ContainerAwareTrait
{
	/**
	 * Container.
	 */
	protected Container $container;

	/**
	 * Array of resolved objects and/or references to resolved objects.
	 */
	protected array $__resolved__ = [];

	/**
	 * Sets the container instance.
	 */
	public function setContainer(Container $container): void
	{
		$this->container = $container;
	}

	/**
	 * Resolves item from the container using overloading.
	 */
	public function __get(string $key): mixed
	{
		if (isset($this->__resolved__[$key])) {
			return $this->__resolved__[$key];
		}

		if ($this->container->has($key) === false) {
			throw new ContainerException(sprintf('Unable to resolve [ %s ].', $key));
		}

		$resolved = $this->container->get($key);

		if ($this->container->isSingleton($key) === false) {
			return $resolved;
		}

		return $this->__resolved__[$key] = $resolved;
	}
}
