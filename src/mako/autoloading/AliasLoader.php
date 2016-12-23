<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\autoloading;

/**
 * Alias loader.
 *
 * @author Frederic G. Østby
 */
class AliasLoader
{
	/**
	 * Class aliases.
	 *
	 * @var array
	 */
	protected $aliases;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param array $aliases Class aliases
	 */
	public function __construct(array $aliases)
	{
		$this->aliases = $aliases;
	}

	/**
	 * Autoloads aliased classes.
	 *
	 * @access public
	 * @param  string $alias Class alias
	 * @return bool
	 */
	public function load(string $alias): bool
	{
		if(array_key_exists($alias, $this->aliases))
		{
			return class_alias($this->aliases[$alias], $alias);
		}

		return false;
	}
}
