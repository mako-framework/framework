<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use \mako\core\Config;

/**
 * Database exists plugin.
 *
 * @author  Frederic G. Østby
 */

class ConfigKeyExistsValidator extends \mako\validator\plugins\ValidatorPlugin implements \mako\validator\plugins\ValidatorPluginInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Rule name.
	 *
	 * @var string
	 */

	protected $ruleName = 'config_key_exists';

	/**
	 * Config instance.
	 *
	 * @var \mako\core\Config
	 */

	protected $config;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\core\Config  $config  Config instance
	 */

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Validator.
	 *
	 * @access  public
	 * @param   string   $input       Input
	 * @param   array    $parameters  Parameters
	 * @return  boolean
	 */

	public function validate($input, $parameters)
	{
		return (array_key_exists($input, $this->config->get($parameters[0])));
	}
}