<?php

namespace mako;

use \mako\HTML;

/**
* Asset manager.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class Assets
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Array of asset groups.
	*
	* @var array
	*/

	protected static $groups = array();

	/**
	* Array of CSS assets.
	*
	* @var array
	*/

	protected $css = array();

	/**
	* Array of JavaScript assets.
	*
	* @var array
	*/

	protected $js = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Constructor.
	*
	* @access  protected
	*/

	public function __construct()
	{
		// Nothing here
	}

	/**
	* Returns the instance of the chosen asset group.
	*
	* @access  public
	* @param   string       Group name
	* @return  mako\Assets
	*/

	public static function group($name = 'default')
	{
		if(!isset(static::$groups[$name]))
		{
			static::$groups[$name] = new static();
		}

		return static::$groups[$name];
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Add an asset.
	*
	* @access  public
	* @param   string  Asset name
	* @param   string  Asset source
	* @param   array   (optional) Asset attributes
	*/

	protected function add($name, $source, array $attributes = array())
	{
		// Prefix source with asset view tag if it's not a URL

		if(strpos($source, '://') === false)
		{
			$source = '[mako:assets]' . $source;
		}

		if(pathinfo(strtok($source, '?'), PATHINFO_EXTENSION) === 'css')
		{
			!isset($attributes['media']) && $attributes['media'] = 'all';

			defined('MAKO_XHTML') && $attributes['type'] = 'text/css';

			$this->css[$name] = array('href' => $source, 'rel' => 'stylesheet') + $attributes;
		}
		else
		{
			defined('MAKO_XHTML') && $attributes['type'] = 'text/javascript';

			$this->js[$name] = array('src' => $source) + $attributes;
		}
	}

	/**
	* Get one or all CSS assets.
	*
	* @access  public
	* @param   string  (optional) Asset name
	* @return  string
	*/

	protected function css($name = null)
	{
		if($name === null)
		{
			$css = array();

			foreach($this->css as $key => $value)
			{
				$css[] = $this->css($key);
			}

			return implode("\n", $css);
		}

		if(!isset($this->css[$name]))
		{
			return null;
		}

		return HTML::tag('link', $this->css[$name]);
	}

	/**
	* Get one or all JavaScript assets.
	*
	* @access  public
	* @param   string  (optional) Asset name
	* @return  string
	*/

	protected function js($name = null)
	{
		if($name === null)
		{
			$js = array();

			foreach($this->js as $key => $value)
			{
				$js[] = $this->js($key);
			}

			return implode("\n", $js);
		}

		if(!isset($this->js[$name]))
		{
			return null;
		}

		return HTML::tag('script', $this->js[$name], '');
	}

	/**
	* Get all assets.
	*
	* @access  public
	* @return  string
	*/

	protected function all()
	{
		return trim(implode("\n\n", array($this->css(), $this->js())));
	}

	/**
	* Performs calls on the chosen group instance.
	*
	* @access  public
	* @param   string  Method name
	* @param   array   Method arguments
	* @return  mixed
	*/

	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this, $name), $arguments);
	}

	/**
	* Performs calls on the default group instance.
	*
	* @access  public
	* @param   string  Method name
	* @param   array   Method arguments
	* @return  mixed
	*/

	public static function __callStatic($name, $arguments)
	{
		return call_user_func_array(array(static::group(), $name), $arguments);
	}
}

/** -------------------- End of file --------------------**/