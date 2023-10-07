<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe\traits;

use mako\syringe\Container;
use mako\syringe\exceptions\ContainerException;

use function vsprintf;

/**
 * Container aware trait.
 *
 * @property \mako\application\Application                      $app
 * @property \mako\bus\command\CommandBusInterface              $commandBus
 * @property \mako\bus\event\EventBusInterface                  $eventBus
 * @property \mako\bus\query\QueryBusInterface                  $queryBus
 * @property \mako\cache\CacheManager                           $cache
 * @property \mako\cli\input\Input                              $input
 * @property \mako\cli\output\Output                            $output
 * @property \mako\config\Config                                $config
 * @property \mako\database\ConnectionManager                   $database
 * @property \mako\error\ErrorHandler                           $errorHandler
 * @property \mako\file\FileSystem                              $fileSystem
 * @property \mako\gatekeeper\authorization\AuthorizerInterface $authorizer
 * @property \mako\gatekeeper\Gatekeeper                        $gatekeeper
 * @property \mako\http\Request                                 $request
 * @property \mako\http\Response                                $response
 * @property \mako\http\routing\Routes                          $routes
 * @property \mako\http\routing\URLBuilder                      $urlBuilder
 * @property \mako\i18n\I18n                                    $i18n
 * @property \mako\logger\Logger                                $logger
 * @property \mako\pagination\PaginationFactoryInterface        $pagination
 * @property \mako\redis\ConnectionManager                      $redis
 * @property \mako\security\crypto\CryptoManager                $crypto
 * @property \mako\security\Signer                              $signer
 * @property \mako\session\Session                              $session
 * @property \mako\utility\Humanizer                            $humanizer
 * @property \mako\validator\ValidatorFactory                   $validator
 * @property \mako\view\ViewFactory                             $view
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
			throw new ContainerException(vsprintf('Unable to resolve [ %s ].', [$key]));
		}

		$resolved = $this->container->get($key);

		if ($this->container->isSingleton($key) === false) {
			return $resolved;
		}

		return $this->__resolved__[$key] = $resolved;
	}
}
