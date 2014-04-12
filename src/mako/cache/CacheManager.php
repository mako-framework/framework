<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache;

use \RuntimeException;

use \mako\cache\adapters\APC;
use \mako\cache\adapters\APCU;
use \mako\cache\adapters\Database;
use \mako\cache\adapters\File;
use \mako\cache\adapters\Memcache;
use \mako\cache\adapters\Memcached;
use \mako\cache\adapters\Memory;
use \mako\cache\adapters\Null;
use \mako\cache\adapters\Redis;
use \mako\cache\adapters\WinCache;
use \mako\cache\adapters\XCache;
use \mako\cache\adapters\ZendDisk;
use \mako\cache\adapters\ZendMemory;

/**
 * Cache manager.
 *
 * @author  Frederic G. Østby
 */

class CacheManager extends \mako\common\AdapterManager
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * APC adapter factory.
	 * 
	 * @access  protected
	 * @param   array                     $configuration  Configuration
	 * @return  \mako\cache\adapters\APC
	 */

	protected function apcAdapter($configuration)
	{
		return new APC();
	}

	/**
	 * APCU adapter factory.
	 * 
	 * @access  protected
	 * @param   array                      $configuration  Configuration
	 * @return  \mako\cache\adapters\APCU
	 */

	protected function apcuAdapter($configuration)
	{
		return new APCU();
	}

	/**
	 * File adapter factory.
	 * 
	 * @access  protected
	 * @param   array                      $configuration  Configuration
	 * @return  \mako\cache\adapters\File
	 */

	protected function fileAdapter($configuration)
	{
		return new File($configuration['path']);
	}

	/**
	 * Database adapter factory.
	 * 
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\adapters\Database
	 */

	protected function databaseAdapter($configuration)
	{
		return new Database($this->container->get('database')->connection($configuration['configuration']), $configuration['table']);
	}

	/**
	 * Memcache adapter factory.
	 * 
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\adapters\Memcache
	 */

	protected function memcacheAdapter($configuration)
	{
		return new Memcache($configuration['servers'], $configuration['timeout'], $configuration['compress_data']);
	}

	/**
	 * Memcached adapter factory.
	 * 
	 * @access  protected
	 * @param   array                           $configuration  Configuration
	 * @return  \mako\cache\adapters\Memcached
	 */

	protected function memcachedAdapter($configuration)
	{
		return new Memcached($configuration['servers'], $configuration['timeout'], $configuration['compress_data']);
	}

	/**
	 * Memory adapter factory.
	 * 
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\adapters\Memory
	 */

	protected function memoryAdapter($configuration)
	{
		return new Memory();
	}

	/**
	 * Null adapter factory.
	 * 
	 * @access  protected
	 * @param   array                        $configuration  Configuration
	 * @return  \mako\cache\adapters\Null
	 */

	protected function nullAdapter($configuration)
	{
		return new Null();
	}

	/**
	 * Redis adapter factory.
	 * 
	 * @access  protected
	 * @param   array                       $configuration  Configuration
	 * @return  \mako\cache\adapters\Redis
	 */

	protected function redisAdapter($configuration)
	{
		return new Redis($this->container->get('redis')->connection($configuration['configuration']));
	}

	/**
	 * Windows cache adapter factory.
	 * 
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\adapters\WinCache
	 */

	protected function wincacheAdapter($configuration)
	{
		return new WinCache();
	}

	/**
	 * Xcache adapter factory.
	 * 
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\adapters\XCache
	 */

	protected function xcacheAdapter($configuration)
	{
		return new XCache($configuration['username'], $configuration['password']);
	}

	/**
	 * Zend disk adapter factory.
	 * 
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\adapters\ZendDisk
	 */

	protected function zenddiskAdapter($configuration)
	{
		return new ZendDisk();
	}

	/**
	 * Zend memory adapter factory.
	 * 
	 * @access  protected
	 * @param   array                            $configuration  Configuration
	 * @return  \mako\cache\adapters\ZendMemory
	 */

	protected function zendmemoryAdapter($configuration)
	{
		return new ZendMemory();
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
			throw new RuntimeException(vsprintf("%s(): [ %s ] has not been defined in the cache configuration.", [__METHOD__, $connection]));
		}

		$configuration = $this->configurations[$configuration];

		$factoryMethod = $this->getFactoryMethodName($configuration['type']);

		return new Cache($this->$factoryMethod($configuration), $configuration['prefix']);
	}
}