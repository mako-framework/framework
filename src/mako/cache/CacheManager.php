<?php

namespace mako\cache;

use \RuntimeException;

use \mako\cache\adapters\Database;
use \mako\cache\adapters\Redis;

/**
 * Cache manager.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
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
	 * Database adapter factory.
	 * 
	 * @access  protected
	 * @param   array                          $configuration  Configuration
	 * @return  \mako\cache\adapters\Database
	 */

	protected function databaseAdapter($configuration)
	{
		return new Database($this->syringe->get('database')->connection($configuration['configuration']), $configuration['table']);
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
		return new Redis($this->syringe->get('redis')->connection($configuration['configuration']));
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

/** -------------------- End of file -------------------- **/