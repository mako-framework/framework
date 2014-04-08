<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core;

/**
 * Alias loader.
 * 
 * @author  Frederic G. Østby
 */

class AliasLoader
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Class aliases.
	 * 
	 * @var array
	 */

	protected $aliases;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   array   $aliases  Class aliases
	 */

	public function __construct(array $aliases)
	{
		$this->aliases = $aliases;
	}

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Autoloads aliased classes.
	 * 
	 * @access  public
	 * @param   string  $alias  Class alias
	 * @return  boolean
	 */

	public function load($alias)
	{
		$alias = ltrim($alias, '\\');

		if(array_key_exists($alias, $this->aliases))
		{
			return class_alias($this->aliases[$alias], $alias);
		}

		return false;
	}
}