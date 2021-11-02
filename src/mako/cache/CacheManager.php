<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache;

use mako\cache\exceptions\CacheException;
use mako\cache\stores\APCU;
use mako\cache\stores\Database;
use mako\cache\stores\File;
use mako\cache\stores\Memcache;
use mako\cache\stores\Memcached;
use mako\cache\stores\Memory;
use mako\cache\stores\NullStore;
use mako\cache\stores\Redis;
use mako\cache\stores\StoreInterface;
use mako\cache\stores\WinCache;
use mako\common\AdapterManager;
use mako\database\ConnectionManager as DatabaseConnectionManager;
use mako\file\FileSystem;
use mako\redis\ConnectionManager as RedisConnectionManager;
use mako\syringe\Container;

use function vsprintf;

/**
 * Cache manager.
 *
 * @mixin \mako\cache\stores\StoreInterface
 * @method \mako\cache\stores\StoreInterface instance($configuration = null)
 * @method \mako\cache\stores\StoreInterface getInstance($configuration = null)
 */
class CacheManager extends AdapterManager
{
	/**
	 * Class whitelist.
	 *
	 * @var array|bool
	 */
	protected $classWhitelist;

	/**
	 * Constructor.
	 *
	 * @param string                  $default        Default connection name
	 * @param array                   $configurations Configurations
	 * @param \mako\syringe\Container $container      Container
	 * @param array|bool              $classWhitelist Class whitelist
	 */
	public function __construct(string $default, array $configurations, Container $container, $classWhitelist = false)
	{
		parent::__construct($default, $configurations, $container);

		$this->classWhitelist = $classWhitelist;
	}

	/**
	 * APCU store factory.
	 *
	 * @param  array                   $configuration Configuration
	 * @return \mako\cache\stores\APCU
	 */
	protected function apcuFactory(array $configuration): APCU
	{
		return (new APCU)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * File store factory.
	 *
	 * @param  array                   $configuration Configuration
	 * @return \mako\cache\stores\File
	 */
	protected function fileFactory(array $configuration): File
	{
		return (new File($this->container->get(FileSystem::class), $configuration['path'], $this->classWhitelist))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Database store factory.
	 *
	 * @param  array                       $configuration Configuration
	 * @return \mako\cache\stores\Database
	 */
	protected function databaseFactory(array $configuration): Database
	{
		return (new Database($this->container->get(DatabaseConnectionManager::class)->getConnection($configuration['configuration']), $configuration['table'], $this->classWhitelist))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Memcache store factory.
	 *
	 * @param  array                       $configuration Configuration
	 * @return \mako\cache\stores\Memcache
	 */
	protected function memcacheFactory(array $configuration): Memcache
	{
		return (new Memcache($configuration['servers'], $configuration['timeout'], $configuration['compress_data']))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Memcached store factory.
	 *
	 * @param  array                        $configuration Configuration
	 * @return \mako\cache\stores\Memcached
	 */
	protected function memcachedFactory(array $configuration): Memcached
	{
		return (new Memcached($configuration['servers'], $configuration['timeout'], $configuration['compress_data']))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Memory store factory.
	 *
	 * @param  array                     $configuration Configuration
	 * @return \mako\cache\stores\Memory
	 */
	protected function memoryFactory(array $configuration): Memory
	{
		return (new Memory)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Redis store factory.
	 *
	 * @param  array                    $configuration Configuration
	 * @return \mako\cache\stores\Redis
	 */
	protected function redisFactory(array $configuration): Redis
	{
		return (new Redis($this->container->get(RedisConnectionManager::class)->getConnection($configuration['configuration']), $this->classWhitelist))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Null store factory.
	 *
	 * @param  array                        $configuration Configuration
	 * @return \mako\cache\stores\NullStore
	 */
	protected function nullFactory(array $configuration): NullStore
	{
		return (new NullStore)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Windows cache store factory.
	 *
	 * @param  array                       $configuration Configuration
	 * @return \mako\cache\stores\WinCache
	 */
	protected function wincacheFactory(array $configuration): WinCache
	{
		return (new WinCache)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Returns a cache instance.
	 *
	 * @param  string                            $configuration Configuration name
	 * @return \mako\cache\stores\StoreInterface
	 */
	protected function instantiate(string $configuration)
	{
		if(!isset($this->configurations[$configuration]))
		{
			throw new CacheException(vsprintf('[ %s ] has not been defined in the cache configuration.', [$configuration]));
		}

		$configuration = $this->configurations[$configuration];

		return $this->factory($configuration['type'], $configuration);
	}

	/**
	 * Returns an instance of the chosen cache store. Alias of CacheManager::getInstance().
	 *
	 * @param  string|null                       $configuration Configuration name
	 * @return \mako\cache\stores\StoreInterface
	 */
	public function getStore(?string $configuration = null): StoreInterface
	{
		return $this->getInstance($configuration);
	}
}
