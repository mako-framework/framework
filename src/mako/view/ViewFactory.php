<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view;

use \RuntimeException;

use \mako\view\renderers\CacheableInterface;

/**
 * View factory.
 *
 * @author  Frederic G. Østby
 */

class ViewFactory
{
	/**
	 * Application path.
	 * 
	 * @var string
	 */

	protected $applicationPath;

	/**
	 * Charset.
	 * 
	 * @var string
	 */

	protected $charset;

	/**
	 * View renderers.
	 * 
	 * @var array
	 */

	protected $renderers =
	[
		'.tpl.php' => 'mako\view\renderers\Template',
		'.php'     => 'mako\view\renderers\PHP',
	];

	/**
	 * Global view variables.
	 * 
	 * @var array
	 */

	protected $globalVariables = [];

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $applicationPath  Application path
	 * @param   string  $charset          (optional) Charset
	 */

	public function __construct($applicationPath, $charset = 'UTF-8')
	{
		$this->applicationPath = $applicationPath;

		$this->globalVariables['__viewfactory__'] = $this;

		$this->setCharset($charset);
	}

	/**
	 * Returns the charset.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * Sets the charset.
	 * 
	 * @access  public
	 * @param   string                  $charset  Charset
	 * @return  \mako\view\ViewFactory
	 */

	public function setCharset($charset)
	{
		$this->globalVariables['__charset__'] = $this->charset = $charset;

		return $this;
	}

	/**
	 * Assign a global view variable that will be available in all views.
	 *
	 * @access  public
	 * @param   string                  $name   Variable name
	 * @param   mixed                   $value  View variable
	 * @return  \mako\view\ViewFactory
	 */

	public function assign($name, $value)
	{
		$this->globalVariables[$name] = $value;

		return $this;
	}

	/**
	 * Returns an array containing the view path and the renderer we should use.
	 * 
	 * @access  protected
	 * @param   string     $view  View
	 * @return  array
	 */

	protected function getView($view)
	{
		// Loop throught the avaiable extensions and check if the view exists

		foreach($this->renderers as $extension => $renderer)
		{
			if(file_exists($path = mako_path($this->applicationPath, 'views', $view, $extension)))
			{
				return ['path' => $path, 'renderer' => $renderer];
			}
		}

		// We didn't find the view so we'll throw an exception

		throw new RuntimeException(vsprintf("%s(): The [ %s ] view does not exist.", [__METHOD__, $view]));
	}

	/**
	 * Creates and returns a view renderer instance.
	 * 
	 * @access  public
	 * @param   string                                  $view       View
	 * @param   array                                   $variables  View variables
	 * @return  \mako\view\renderers\RendererInterface
	 */

	public function create($view, array $variables = [])
	{
		$view = str_replace('.', '/', $view);

		$view = $this->getView($view);

		$renderer = $view['renderer'];

		// Create renderer instance

		$renderer = new $renderer($view['path'], array_merge($this->globalVariables, $variables));

		// Set the view cache path if needed

		if($renderer instanceof CacheableInterface)
		{
			$renderer->setCachePath($this->applicationPath . '/storage/cache/views');
		}

		// Return the renderer

		return $renderer;
	}
}