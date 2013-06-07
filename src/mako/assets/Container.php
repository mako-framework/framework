<?php

namespace mako\assets;

use \mako\HTML;
use \mako\Assets;

/**
 * Asset container.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Container
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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
	 * @access  public
	 */

	public function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Add an asset.
	 *
	 * @access  public
	 * @param   string  $name        Asset name
	 * @param   string  $source      Asset source
	 * @param   array   $attributes  (optional) Asset attributes
	 */

	public function add($name, $source, array $attributes = array())
	{
		// Prefix source with asset location if it's not a URL

		if(strpos($source, '://') === false)
		{
			$source = Assets::location() . $source;
		}

		if(pathinfo(strtok($source, '?'), PATHINFO_EXTENSION) === 'css')
		{
			defined('MAKO_XHTML') && $attributes['type'] = 'text/css';

			$this->css[$name] = $attributes + array('href' => $source, 'rel' => 'stylesheet', 'media' => 'all');
		}
		else
		{
			defined('MAKO_XHTML') && $attributes['type'] = 'text/javascript';

			$this->js[$name] = $attributes + array('src' => $source);
		}
	}

	/**
	 * Get one or all CSS assets.
	 *
	 * @access  public
	 * @param   string  $name  (optional) Asset name
	 * @return  string
	 */

	public function css($name = null)
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
	 * @param   string  $name  (optional) Asset name
	 * @return  string
	 */

	public function js($name = null)
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

	public function all()
	{
		return trim(implode("\n\n", array($this->css(), $this->js())));
	}
}

/** -------------------- End of file -------------------- **/