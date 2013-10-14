<?php

namespace mako\utility\assets;

use \mako\utility\HTML;
use \mako\utility\assets\Assets;

/**
 * Asset container.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Group
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
	 * Prefix the source with asset location if it's not a URL.
	 * 
	 * @access  protected
	 * @param   string     $source  Asset source
	 * @return  string
	 */

	protected function prefixSource($source)
	{
		if(strpos($source, '://') === false && substr($source, 0, 2) !== '//')
		{
			$source = Assets::location() . $source;
		}
		
		return $source;
	}

	/**
	 * Add a CSS asset to the group.
	 * 
	 * @param   string                      $source      Asset source
	 * @param   array                       $attributes  (optional) Asset attributes
	 * @param   string                      $name        (optional) Asset name
	 * @return  \mako\utility\assets\Group
	 */

	public function addCSS($source, array $attributes = array(), $name = null)
	{
		$source = $this->prefixSource($source);

		defined('MAKO_XHTML') && $attributes['type'] = 'text/css';

		$attributes = $attributes + array('href' => $source, 'rel' => 'stylesheet', 'media' => 'all');

		if($name === null)
		{
			$this->css[] = $attributes;
		}
		else
		{
			$this->css[$name] = $attributes;
		}

		return $this;
	}

	/**
	 * Add a JS asset to the group.
	 * 
	 * @param   string                      $source      Asset source
	 * @param   array                       $attributes  (optional) Asset attributes
	 * @param   string                      $name        (optional) Asset name
	 * @return  \mako\utility\assets\Group
	 */

	public function addJS($source, array $attributes = array(), $name = null)
	{
		$source = $this->prefixSource($source);

		defined('MAKO_XHTML') && $attributes['type'] = 'text/javascript';

		$attributes = $attributes + array('src' => $source);

		if($name === null)
		{
			$this->js[] = $attributes;
		}
		else
		{
			$this->js[$name] = $attributes;
		}

		return $this;
	}

	/**
	 * Add an asset.
	 *
	 * @access  public
	 * @param   string                      $source      Asset source
	 * @param   array                       $attributes  (optional) Asset attributes
	 * @param   string                      $name        (optional) Asset name
	 * @param   string                      $type        (optional) Asset type
	 * @return  \mako\utility\assets\Group
	 */

	public function add($source, array $attributes = array(), $name = null, $type = null)
	{
		if($type === 'css' || pathinfo(strtok($source, '?'), PATHINFO_EXTENSION) === 'css')
		{
			return $this->addCSS($source, $attributes, $name);
		}
		else
		{
			return $this->addJS($source, $attributes, $name);
		}
	}

	/**
	 * Get one or all CSS assets.
	 *
	 * @access  public
	 * @param   string  $name  (optional) Asset name
	 * @return  string
	 */

	public function CSS($name = null)
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

	public function JS($name = null)
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