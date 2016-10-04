<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\view;

use Closure;
use RuntimeException;

use mako\common\NamespacedFileLoaderTrait;
use mako\file\FileSystem;
use mako\syringe\Container;
use mako\view\renderers\PHP;
use mako\view\renderers\RendererInterface;

/**
 * View factory.
 *
 * @author  Frederic G. Østby
 */
class ViewFactory
{
	use NamespacedFileLoaderTrait;

	/**
	 * File sytem.
	 *
	 * @var \mako\file\FileSystem
	 */
	protected $fileSystem;

	/**
	 * Path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Charset.
	 *
	 * @var string
	 */
	protected $charset;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * View renderers.
	 *
	 * @var array
	 */
	protected $renderers = ['.php' => PHP::class];

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
	 * @param   \mako\file\FileSystem         $fileSystem  File system instance
	 * @param   string                        $path        Default path
	 * @param   string                        $charset     Charset
	 * @param   null|\mako\syringe\Container  $container   Container
	 */
	public function __construct(FileSystem $fileSystem, string $path, string $charset = 'UTF-8', Container $container = null)
	{
		$this->fileSystem = $fileSystem;

		$this->path = $path;

		$this->globalVariables['__viewfactory__'] = $this;

		$this->setCharset($charset);

		$this->container = $container ?? new Container;
	}

	/**
	 * Returns the charset.
	 *
	 * @access  public
	 * @return  string
	 */
	public function getCharset(): string
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
	public function setCharset(string $charset): ViewFactory
	{
		$this->globalVariables['__charset__'] = $this->charset = $charset;

		return $this;
	}

	/**
	 * Registers a custom view renderer.
	 *
	 * @access  public
	 * @param   string                  $extension  Extension handled by the renderer
	 * @param   string|\Closure         $renderer   Renderer class or closure that creates a renderer instance
	 * @return  \mako\view\ViewFactory
	 */
	public function extend(string $extension, $renderer): ViewFactory
	{
		$this->renderers = [$extension => $renderer] + $this->renderers;

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
	public function assign(string $name, $value): ViewFactory
	{
		$this->globalVariables[$name] = $value;

		return $this;
	}

	/**
	 * Returns an array containing the view path and the renderer we should use.
	 *
	 * @access  protected
	 * @param   string      $view            View
	 * @param   bool        $throwException  Throw exception if view doesn't exist?
	 * @return  array|bool
	 */
	protected function getViewPathAndExtension(string $view, bool $throwException = true)
	{
		if(!isset($this->viewCache[$view]))
		{
			// Loop throught the avaiable extensions and check if the view exists

			foreach($this->renderers as $extension => $renderer)
			{
				$paths = $this->getCascadingFilePaths($view, $extension);

				foreach($paths as $path)
				{
					if($this->fileSystem->has($path))
					{
						return $this->viewCache[$view] = [$path, $extension];
					}
				}
			}

			// We didn't find the view so we'll throw an exception or return false

			if($throwException)
			{
				throw new RuntimeException(vsprintf("%s(): The [ %s ] view does not exist.", [__METHOD__, $view]));
			}

			return false;
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
	protected function rendererFactory($renderer): RendererInterface
	{
		return $renderer instanceof Closure ? $this->container->call($renderer) : $this->container->get($renderer);
	}

	/**
	 * Returns a renderer instance.
	 *
	 * @access  protected
	 * @param   string                                  $extension  Extension associated with the renderer
	 * @return  \mako\view\renderers\RendererInterface
	 */
	protected function resolveRenderer(string $extension): RendererInterface
	{
		if(!isset($this->rendererInstances[$extension]))
		{
			$this->rendererInstances[$extension] = $this->rendererFactory($this->renderers[$extension]);
		}

		return $this->rendererInstances[$extension];
	}

	/**
	 * Returns TRUE if the view exists and false if not.
	 *
	 * @access  public
	 * @param   string  $view  View
	 * @return  bool
	 */
	public function exists(string $view): bool
	{
		return $this->getViewPathAndExtension($view, false) !== false;
	}

	/**
	 * Creates and returns a view instance.
	 *
	 * @access  public
	 * @param   string           $view       View
	 * @param   array            $variables  View variables
	 * @return  \mako\view\View
	 */
	public function create(string $view, array $variables = []): View
	{
		list($path, $extension) = $this->getViewPathAndExtension($view);

		return new View($path, $variables + $this->globalVariables, $this->resolveRenderer($extension));
	}

	/**
	 * Creates and returns a rendered view.
	 *
	 * @access  public
	 * @param   string  $view       View
	 * @param   array   $variables  View variables
	 * @return  string
	 */
	public function render(string $view, array $variables = []): string
	{
		return $this->create($view, $variables)->render();
	}
}