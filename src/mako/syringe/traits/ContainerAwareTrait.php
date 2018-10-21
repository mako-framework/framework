<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe\traits;

use mako\syringe\Container;
use RuntimeException;

use function vsprintf;

/**
 * Container aware trait.
 *
 * @author Frederic G. Østby
 *
 * @property \mako\application\Application               $app
 * @property \mako\cli\input\Input                       $input
 * @property \mako\cli\output\Output                     $output
 * @property \mako\commander\CommandBusInterface         $bus
 * @property \mako\file\FileSystem                       $fileSystem
 * @property \mako\config\Config                         $config
 * @property \mako\cache\CacheManager                    $cache
 * @property \mako\security\crypto\CryptoManager         $crypto
 * @property \mako\database\ConnectionManager            $database
 * @property \mako\event\Event                           $event
 * @property \mako\error\ErrorHandler                    $errorHandler
 * @property \mako\gatekeeper\Authentication             $gatekeeper
 * @property \mako\utility\Humanizer                     $humanizer
 * @property \mako\i18n\I18n                             $i18n
 * @property \Psr\Log\LoggerInterface                    $logger
 * @property \mako\pagination\PaginationFactoryInterface $pagination
 * @property \mako\redis\ConnectionManager               $redis
 * @property \mako\http\Request                          $request
 * @property \mako\http\Response                         $response
 * @property \mako\http\routing\Routes                   $routes
 * @property \mako\session\Session                       $session
 * @property \mako\security\Signer                       $signer
 * @property \mako\http\routing\URLBuilder               $urlBuilder
 * @property \mako\validator\ValidatorFactory            $validator
 * @property \mako\view\ViewFactory                      $view
 */
trait ContainerAwareTrait
{
	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Array of resolved objects and/or references to resolved objects.
	 *
	 * @var array
	 */
	protected $resolved = [];

	/**
	 * Sets the container instance.
	 *
	 * @param \mako\syringe\Container $container Container
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolves item from the container using overloading.
	 *
	 * @param  string $key Key
	 * @return mixed
	 */
	public function __get(string $key)
	{
		if(isset($this->resolved[$key]))
		{
			return $this->resolved[$key];
		}

		if($this->container->has($key) === false)
		{
			throw new RuntimeException(vsprintf('Unable to resolve [ %s ].', [$key]));
		}

		$resolved = $this->container->get($key);

		if($this->container->isSingleton($key) === false)
		{
			return $resolved;
		}

		return $this->resolved[$key] = $resolved;
	}
}
