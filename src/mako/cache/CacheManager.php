<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache;

use RuntimeException;

use mako\cache\stores\APCU;
use mako\cache\stores\Database;
use mako\cache\stores\File;
use mako\cache\stores\Memcache;
use mako\cache\stores\Memcached;
use mako\cache\stores\Memory;
use mako\cache\stores\NullStore;
use mako\cache\stores\Redis;
use mako\cache\stores\WinCache;
use mako\cache\stores\ZendDisk;
use mako\cache\stores\ZendMemory;
use mako\common\AdapterManager;
use mako\syringe\Container;

/**
 * Cache manager.
 *
 * @author  Frederic G. Østby
 *
 * @method  \mako\cache\Cache  instance($configuration = null)
 * @method  bool               put(string $key, $data, int $ttl = 0)
 * @method  bool               has(string $key)
 * @method  mixed              get(string $key)
 * @method  mixed              getOrElse(string $key, callable $data, int $ttl = 0)
 * @method  mixed              getAndPut(string $key, $data, int $ttl = 0)
 * @method  mixed              getAndRemove(string $key)
 * @method  bool               remove(string $key)
 * @method  bool               clear()
 */
class CacheManager extends AdapterManager
{
	/**
	 * Class whitelist.
	 *
	 * @var bool|array
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   string                   $default         Default connection name
	 * @param   array                    $configurations  Configurations
	 * @param   \mako\syringe\Container  $container       IoC container instance
	 * @param   bool|array               $classWhitelist  Class whitelist
	 */
	public function __construct(string $default, array $configurations, Container $container, $classWhitelist = false)
	{
		parent::__construct($default, $configurations, $container);

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * APCU store factory.
	 *
	 * @access  protected
	 * @param   array                    $configuration  Configuration
	 * @return  \mako\cache\stores\APCU
	 */
	protected function apcuFactory(array $configuration): APCU
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
	protected function fileFactory(array $configuration): File
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
	protected function databaseFactory(array $configuration): Database
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
	protected function memcacheFactory(array $configuration): Memcache
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
	protected function memcachedFactory(array $configuration): Memcached
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
	protected function memoryFactory(array $configuration): Memory
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
	protected function redisFactory(array $configuration): Redis
	{
		return new Redis($this->container->get('redis')->connection($configuration['configuration'], $this->classWhitelist));
	}

	/**
	 * Null store factory.
	 *
	 * @access  protected
	 * @param   array                         $configuration  Configuration
	 * @return  \mako\cache\stores\NullStore
	 */
	protected function nullFactory(array $configuration): NullStore
	{
		return new NullStore;
	}

	/**
	 * Windows cache store factory.
	 *
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\stores\WinCache
	 */
	protected function wincacheFactory(array $configuration): WinCache
	{
		return new WinCache;
	}

	/**
	 * Zend disk store factory.
	 *
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\stores\ZendDisk
	 */
	protected function zenddiskFactory(array $configuration): ZendDisk
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
	protected function zendmemoryFactory(array $configuration): ZendMemory
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
	protected function instantiate(string $configuration): Cache
	{
		if(!isset($this->configurations[$configuration]))
		{
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the cache configuration.", [__METHOD__, $configuration]));
		}

		$configuration = $this->configurations[$configuration];

		return new Cache($this->factory($configuration['type'], $configuration), $configuration['prefix'] ?? null);
	}
}