<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\view;

use Closure;
use mako\common\traits\NamespacedFileLoaderTrait;
use mako\file\FileSystem;
use mako\syringe\Container;
use mako\view\exceptions\ViewException;
use mako\view\renderers\PHP;
use mako\view\renderers\RendererInterface;

use function vsprintf;

/**
 * View factory.
 */
class ViewFactory
{
	use NamespacedFileLoaderTrait;

	/**
	 * Charset.
	 */
	protected string $charset;

	/**
	 * View renderers.
	 */
	protected array $renderers = ['.php' => PHP::class];

	/**
	 * Global view variables.
	 */
	protected array $globalVariables = [];

	/**
	 * Variables that should be auto assigned to views.
	 */
	protected array $autoAssignVariables = [];

	/**
	 * View cache.
	 */
	protected array $viewCache = [];

	/**
	 * Renderer instances.
	 */
	protected array $rendererInstances = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected FileSystem $fileSystem,
		string $path,
		string $charset = 'UTF-8',
		protected Container $container = new Container
	)
	{
		$this->path = $path;

		$this->globalVariables['__viewfactory__'] = $this;

		$this->setCharset($charset);
	}

	/**
	 * Returns the charset.
	 */
	public function getCharset(): string
	{
		return $this->charset;
	}

	/**
	 * Sets the charset.
	 */
	public function setCharset(string $charset): ViewFactory
	{
		$this->globalVariables['__charset__'] = $this->charset = $charset;

		return $this;
	}

	/**
	 * Registers a custom view renderer.
	 */
	public function extend(string $extension, Closure|string $renderer): ViewFactory
	{
		$this->renderers = [$extension => $renderer] + $this->renderers;

		return $this;
	}

	/**
	 * Assign a global view variable that will be available in all views.
	 */
	public function assign(string $name, mixed $value): ViewFactory
	{
		$this->globalVariables[$name] = $value;

		return $this;
	}

	/**
	 * Assign variables that should be auto assigned to views upon creation.
	 */
	public function autoAssign($view, callable $variables): ViewFactory
	{
		foreach((array) $view as $name)
		{
			$this->autoAssignVariables[$name] = $variables;
		}

		return $this;
	}

	/**
	 * Clears the autoassign variables.
	 */
	public function clearAutoAssignVariables(): ViewFactory
	{
		$this->autoAssignVariables = [];

		return $this;
	}

	/**
	 * Returns an array containing the view path and the renderer we should use.
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
				throw new ViewException(vsprintf('The [ %s ] view does not exist.', [$view]));
			}

			return false;
		}

		return $this->viewCache[$view];
	}

	/**
	 * Creates a renderer instance.
	 */
	protected function rendererFactory(Closure|string $renderer): RendererInterface
	{
		return $renderer instanceof Closure ? $this->container->call($renderer) : $this->container->get($renderer);
	}

	/**
	 * Returns a renderer instance.
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
	 * Returns TRUE if the view exists and FALSE if not.
	 */
	public function exists(string $view): bool
	{
		return $this->getViewPathAndExtension($view, false) !== false;
	}

	/**
	 * Returns view specific auto assign variables.
	 */
	protected function getAutoAssignVariablesForView(string $view): array
	{
		if(!isset($this->autoAssignVariables[$view]))
		{
			return [];
		}

		return ($this->autoAssignVariables[$view])();
	}

	/**
	 * Returns auto assign variables for a view.
	 */
	protected function getAutoAssignVariables(string $view): array
	{
		return $this->getAutoAssignVariablesForView($view) + $this->getAutoAssignVariablesForView('*');
	}

	/**
	 * Returns array where variables have been merged in order of importance.
	 */
	protected function mergeVariables(string $view, array $variables): array
	{
		return $variables + $this->getAutoAssignVariables($view) + $this->globalVariables;
	}

	/**
	 * Creates and returns a view instance.
	 */
	public function create(string $view, array $variables = []): View
	{
		[$path, $extension] = $this->getViewPathAndExtension($view);

		return new View($path, $this->mergeVariables($view, $variables), $this->resolveRenderer($extension));
	}

	/**
	 * Creates and returns a rendered view.
	 */
	public function render(string $view, array $variables = []): string
	{
		return $this->create($view, $variables)->render();
	}
}
