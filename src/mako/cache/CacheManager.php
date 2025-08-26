<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cache;

use Deprecated;
use mako\cache\exceptions\CacheException;
use mako\cache\stores\APCu;
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
use Override;

use function sprintf;

/**
 * Cache manager.
 *
 * @mixin \mako\cache\stores\StoreInterface
 * @method \mako\cache\stores\StoreInterface getInstance($configuration = null)
 */
class CacheManager extends AdapterManager
{
	/**
	 * Constructor.
	 */
	public function __construct(
		string $default,
		array $configurations,
		Container $container,
		protected array|bool $classWhitelist = false
	) {
		parent::__construct($default, $configurations, $container);
	}

	/**
	 * APCU store factory.
	 */
	protected function apcuFactory(array $configuration): APCu
	{
		return (new APCu)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * File store factory.
	 */
	protected function fileFactory(array $configuration): File
	{
		return (new File($this->container->get(FileSystem::class), $configuration['path'], $this->classWhitelist))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Database store factory.
	 */
	protected function databaseFactory(array $configuration): Database
	{
		return (new Database($this->container->get(DatabaseConnectionManager::class)->getConnection($configuration['configuration']), $configuration['table'], $this->classWhitelist))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Memcache store factory.
	 */
	protected function memcacheFactory(array $configuration): Memcache
	{
		return (new Memcache($configuration['servers'], $configuration['timeout'], $configuration['compress_data']))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Memcached store factory.
	 */
	protected function memcachedFactory(array $configuration): Memcached
	{
		return (new Memcached($configuration['servers'], $configuration['timeout'], $configuration['compress_data']))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Memory store factory.
	 */
	protected function memoryFactory(array $configuration): Memory
	{
		return (new Memory)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Redis store factory.
	 */
	protected function redisFactory(array $configuration): Redis
	{
		return (new Redis($this->container->get(RedisConnectionManager::class)->getConnection($configuration['configuration']), $this->classWhitelist))->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Null store factory.
	 */
	protected function nullFactory(array $configuration): NullStore
	{
		return (new NullStore)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Windows cache store factory.
	 */
	#[Deprecated('use any of the other cache stores', since: 'Mako 11.1.0')]
	protected function wincacheFactory(array $configuration): WinCache
	{
		return (new WinCache)->setPrefix($configuration['prefix'] ?? '');
	}

	/**
	 * Returns a cache instance.
	 */
	#[Override]
	protected function instantiate(string $configuration): StoreInterface
	{
		if (!isset($this->configurations[$configuration])) {
			throw new CacheException(sprintf('[ %s ] has not been defined in the cache configuration.', $configuration));
		}

		$configuration = $this->configurations[$configuration];

		return $this->factory($configuration['type'], $configuration);
	}

	/**
	 * Returns an instance of the chosen cache store. Alias of CacheManager::getInstance().
	 */
	public function getStore(?string $configuration = null): StoreInterface
	{
		return $this->getInstance($configuration);
	}
}
