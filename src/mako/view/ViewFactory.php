<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view;

use \Closure;
use \RuntimeException;

use \mako\common\NamespacedFileLoaderTrait;
use \mako\file\FileSystem;

/**
 * View factory.
 *
 * @author  Frederic G. Østby
 */

class ViewFactory
{
	use NamespacedFileLoaderTrait;

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

	protected $renderers = ['.php' => 'mako\view\renderers\PHP'];

	/**
	 * Cache path.
	 * 
	 * @var string
	 */

	protected $cachePath;

	/**
	 * Global view variables.
	 * 
	 * @var array
	 */

	protected $globalVariables = [];

	/**
	 * View cache.
	 * 
	 * @var array
	 */

	protected $viewCache = [];

	/**
	 * Renderer instances.
	 * 
	 * @var array
	 */

	protected $rendererInstances;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $fileSystem  File system instance
	 * @param   string  $path        Default path
	 * @param   string  $charset     (optional) Charset
	 */

	public function __construct(FileSystem $fileSystem, $path, $charset = 'UTF-8')
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;

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
	 * Prepends a view renderer.
	 * 
	 * @access  protected
	 * @param   string           $extention  Extention handled by the renderer
	 * @param   string|\Closure  $renderer   Renderer class or closure that creates a renderer instance
	 */

	protected function prependRenderer($extention, $renderer)
	{
		$this->renderers = [$extention => $renderer] + $this->renderers;
	}

	/**
	 * Appends a view renderer.
	 * 
	 * @access  protected
	 * @param   string           $extention  Extention handled by the renderer
	 * @param   string|\Closure  $renderer   Renderer class or closure that creates a renderer instance
	 */

	protected function appendRenderer($extention, $renderer)
	{
		$this->renderers =  $this->renderers + [$extention => $renderer];
	}

	/**
	 * Registers a custom view renderer.
	 * 
	 * @access  public
	 * @param   string                  $extention  Extention handled by the renderer
	 * @param   string|\Closure         $renderer   Renderer class or closure that creates a renderer instance
	 * @param   boolean                 $prepend    (optional) Prepend the custom renderer on the stack
	 * @return  \mako\view\ViewFactory
	 */

	public function registerRenderer($extention, $renderer, $prepend = true)
	{
		$prepend ? $this->prependRenderer($extention, $renderer) : $this->appendRenderer($extention, $renderer);

		return $this;
	}

	/**
	 * Returns the cache path.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getCachePatch()
	{
		return $this->cachePath;
	}

	/**
	 * Sets the cache path.
	 * 
	 * @access  public
	 * @param   string
	 * @return  \mako\view\ViewFactory
	 */

	public function setCachePath($cachePath)
	{
		$this->cachePath = $cachePath;

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

	protected function getViewPathAndExtension($view)
	{
		if(!isset($this->viewCache[$view]))
		{
			// Loop throught the avaiable extensions and check if the view exists

			foreach($this->renderers as $extension => $renderer)
			{
				if($this->fileSystem->exists($path = $this->getFilePath($view, $extension)))
				{
					return $this->viewCache[$view] = [$path, $extension];
				}
			}

			// We didn't find the view so we'll throw an exception

			throw new RuntimeException(vsprintf("%s(): The [ %s ] view does not exist.", [__METHOD__, $view]));
		}

		return $this->viewCache[$view];
	}

	/**
	 * Creates a renderer instance.
	 * 
	 * @access  protected
	 * @param   string|\Closure                         $renderer  Renderer class or closure
	 * @return  \mako\view\renderers\RendererInterface
	 */

	protected function rendererFactory($renderer)
	{
		return $renderer instanceof Closure ? $renderer() : new $renderer;
	}

	/**
	 * Returns a renderer instance.
	 * 
	 * @access  protected
	 * @param   string                                  $extension  Extension associated with the renderer
	 * @return  \mako\view\renderers\RendererInterface
	 */

	protected function resolveRenderer($extension)
	{
		if(!isset($this->rendererInstances[$extension]))
		{
			$this->rendererInstances[$extension] = $this->rendererFactory($this->renderers[$extension]);
		}

		return $this->rendererInstances[$extension];
	}

	/**
	 * Creates and returns a view instance.
	 * 
	 * @access  public
	 * @param   string           $view       View
	 * @param   array            $variables  (optional) View variables
	 * @return  \mako\view\View
	 */

	public function create($view, array $variables = [])
	{
		list($path, $extension) = $this->getViewPathAndExtension($view);

		return new View($path, $variables + $this->globalVariables, $this->resolveRenderer($extension));
	}

	/**
	 * Creates and returns a rendered view.
	 * 
	 * @access  public
	 * @param   string  $view       View
	 * @param   array   $variables  (optional) View variables
	 * @return  string
	 */

	public function render($view, array $variables = [])
	{
		return $this->create($view, $variables)->render();
	}
}