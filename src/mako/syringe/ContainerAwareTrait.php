<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\syringe;

use RuntimeException;

use mako\syringe\Container;

/**
 * Container aware trait.
 *
 * @author  Frederic G. Østby
 *
 * @property  \mako\application\Application        $app
 * @property  \mako\cli\input\Input                $input
 * @property  \mako\cli\input\Output               $output
 * @property  \mako\commander\CommandBusInterface  $bus
 * @property  \mako\file\FileSystem                $fileSystem
 * @property  \mako\config\Config                  $config
 * @property  \mako\cache\CacheManager             $cache
 * @property  \mako\security\crypto\CryptoManager  $crypto
 * @property  \mako\database\ConnectionManager     $database
 * @property  \mako\event\Event                    $event
 * @property  \mako\error\ErrorHandler             $errorHandler
 * @property  \mako\auth\Gatekeeper                $gatekeeper
 * @property  \mako\utility\Humanizer              $humanizer
 * @property  \mako\i18n\I18n                      $i18n
 * @property  \Psr\Log\LoggerInterface             $logger
 * @property  \mako\pagination\PaginationFactory   $pagination
 * @property  \mako\redis\ConnectionManager        $redis
 * @property  \mako\http\Request                   $request
 * @property  \mako\http\Response                  $response
 * @property  \mako\http\routing\Routes            $routes
 * @property  \mako\session\Session                $session
 * @property  \mako\security\Signer                $signer
 * @property  \mako\http\routing\URLBuilder        $urlBuilder
 * @property  \mako\validator\ValidatorFactory     $validator
 * @property  \mako\view\ViewFactory               $view
 */

trait ContainerAwareTrait
{
	/**
	 * IoC container instance.
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
	 * @access  public
	 * @param   \mako\syringe\Container  $container  IoC container instance
	 */

	public function setContainer(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Resolves item from the container using overloading.
	 *
	 * @access  public
	 * @param   string  $key  Key
	 * @return  mixed
	 */

	public function __get($key)
	{
		if(!isset($this->resolved[$key]))
		{
			if(!$this->container->has($key))
			{
				throw new RuntimeException(vsprintf("%s::%s(): Unable to resolve [ %s ].", [__TRAIT__, __FUNCTION__, $key]));
			}

			$this->resolved[$key] = $this->container->get($key);
		}

		return $this->resolved[$key];
	}
}