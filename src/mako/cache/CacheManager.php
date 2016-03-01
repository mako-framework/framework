<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache;

use RuntimeException;

use mako\cache\stores\APC;
use mako\cache\stores\APCU;
use mako\cache\stores\Database;
use mako\cache\stores\File;
use mako\cache\stores\Memcache;
use mako\cache\stores\Memcached;
use mako\cache\stores\Memory;
use mako\cache\stores\Redis;
use mako\cache\stores\Void;
use mako\cache\stores\WinCache;
use mako\cache\stores\XCache;
use mako\cache\stores\ZendDisk;
use mako\cache\stores\ZendMemory;
use mako\common\AdapterManager;
use mako\syringe\Container;

/**
 * Cache manager.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\cache\stores\StoreInterface  instance($configuration = null)
 */
class CacheManager extends AdapterManager
{
	/**
	 * Class whitelist.
	 *
	 * @var boolean|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string                   $default         Default connection name
	 * @param   array                    $configurations  Configurations
	 * @param   \mako\syringe\Container  $container       IoC container instance
	 * @param   boolean|array            $classWhitelist  Class whitelist
	 */
	public function __construct($default, array $configurations, Container $container, $classWhitelist = false)
	{
		$this->default = $default;

		$this->configurations = $configurations;

		$this->container = $container;

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * APC store factory.
	 *
	 * @access  protected
	 * @param   array                   $configuration  Configuration
	 * @return  \mako\cache\stores\APC
	 */
	protected function apcFactory($configuration)
	{
		return new APC;
	}

	/**
	 * APCU store factory.
	 *
	 * @access  protected
	 * @param   array                    $configuration  Configuration
	 * @return  \mako\cache\stores\APCU
	 */
	protected function apcuFactory($configuration)
	{
		return new APCU;
	}

	/**
	 * File store factory.
	 *
	 * @access  protected
	 * @param   array                    $configuration  Configuration
	 * @return  \mako\cache\stores\File
	 */
	protected function fileFactory($configuration)
	{
		return new File($this->container->get('fileSystem'), $configuration['path'], $this->classWhitelist);
	}

	/**
	 * Database store factory.
	 *
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\stores\Database
	 */
	protected function databaseFactory($configuration)
	{
		return new Database($this->container->get('database')->connection($configuration['configuration']), $configuration['table'], $this->classWhitelist);
	}

	/**
	 * Memcache store factory.
	 *
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\stores\Memcache
	 */
	protected function memcacheFactory($configuration)
	{
		return new Memcache($configuration['servers'], $configuration['timeout'], $configuration['compress_data']);
	}

	/**
	 * Memcached store factory.
	 *
	 * @access  protected
	 * @param   array                         $configuration  Configuration
	 * @return  \mako\cache\stores\Memcached
	 */
	protected function memcachedFactory($configuration)
	{
		return new Memcached($configuration['servers'], $configuration['timeout'], $configuration['compress_data']);
	}

	/**
	 * Memory store factory.
	 *
	 * @access  protected
	 * @param   array                      $configuration  Configuration
	 * @return  \mako\cache\stores\Memory
	 */
	protected function memoryFactory($configuration)
	{
		return new Memory;
	}

	/**
	 * Redis store factory.
	 *
	 * @access  protected
	 * @param   array                     $configuration  Configuration
	 * @return  \mako\cache\stores\Redis
	 */
	protected function redisFactory($configuration)
	{
		return new Redis($this->container->get('redis')->connection($configuration['configuration'], $this->classWhitelist));
	}

	/**
	 * Void store factory.
	 *
	 * @access  protected
	 * @param   array                    $configuration  Configuration
	 * @return  \mako\cache\stores\Void
	 */
	protected function voidFactory($configuration)
	{
		return new Void;
	}

	/**
	 * Windows cache store factory.
	 *
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\stores\WinCache
	 */
	protected function wincacheFactory($configuration)
	{
		return new WinCache;
	}

	/**
	 * Xcache store factory.
	 *
	 * @access  protected
	 * @param   array                      $configuration  Configuration
	 * @return  \mako\cache\stores\XCache
	 */
	protected function xcacheFactory($configuration)
	{
		return new XCache($configuration['username'], $configuration['password']);
	}

	/**
	 * Zend disk store factory.
	 *
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\stores\ZendDisk
	 */
	protected function zenddiskFactory($configuration)
	{
		return new ZendDisk;
	}

	/**
	 * Zend memory store factory.
	 *
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\stores\ZendMemory
	 */
	protected function zendmemoryFactory($configuration)
	{
		return new ZendMemory;
	}

	/**
	 * Returns a cache instance.
	 *
	 * @access  public
	 * @param   string             $configuration  Configuration name
	 * @return  \mako\cache\Cache
	 */
	protected function instantiate($configuration)
	{
		if(!isset($this->configurations[$configuration]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the cache configuration.", [__METHOD__, $configuration]));
		}

		$configuration = $this->configurations[$configuration];

		$factoryMethod = $this->getFactoryMethodName($configuration['type']);

		return new Cache($this->$factoryMethod($configuration), $configuration['prefix']);
	}
}