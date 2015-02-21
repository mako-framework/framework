<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\syringe;

use mako\syringe\ClassInspector;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

/**
 * Inversion of control container.
 *
 * @author  Frederic G. Østby
 */

class Container
{
	/**
	 * Registered type hints.
	 *
	 * @var array
	 */

	protected $hints = [];

	/**
	 * Aliases.
	 *
	 * @var array
	 */

	protected $aliases = [];

	/**
	 * Singleton instances.
	 *
	 * @var array
	 */

	protected $instances = [];

	/**
	 * Parse the hint parameter.
	 *
	 * @access  protected
	 * @param   string|array  $hint  Type hint or array contaning both type hint and alias
	 * @return  array
	 */

	protected function parseHint($hint)
	{
		if(is_array($hint))
		{
			list($name, $alias) = $hint;

			$this->aliases[$alias] = $name;
		}
		else
		{
			$name = $hint;

			$alias = null;
		}

		return compact('name', 'alias');
	}

	/**
	 * Register a type hint.
	 *
	 * @access  public
	 * @param   string|array     $hint       Type hint or array contaning both type hint and alias
	 * @param   string|\Closure  $class      Class name or closure
	 * @param   boolean          $singleton  Should we return the same instance every time?
	 */

	public function register($hint, $class, $singleton = false)
	{
		$hint = $this->parseHint($hint);

		$this->hints[$hint['name']] = ['class' => $class, 'singleton' => $singleton, 'alias' => $hint['alias']];
	}

	/**
	 * Register a type hint and return the same instance every time.
	 *
	 * @access  public
	 * @param   string|array     $hint   Type hint or array contaning both type hint and alias
	 * @param   string|\Closure  $class  Class name or closure
	 */

	public function registerSingleton($hint, $class)
	{
		$this->register($hint, $class, true);
	}

	/**
	 * Register a singleton instance.
	 *
	 * @access  public
	 * @param   string|array  $hint      Type hint or array contaning both type hint and alias
	 * @param   object        $instance  Class instance
	 */

	public function registerInstance($hint, $instance)
	{
		$hint = $this->parseHint($hint);

		$this->instances[$hint['name']] = $instance;
	}

	/**
	 * Return the name based on its alias. If no alias exists then we'll just return the value we received.
	 *
	 * @access  protected
	 * @param   string     $alias  Alias
	 * @return  string
	 */

	protected function resolveAlias($alias)
	{
		$alias = ltrim($alias, '\\');

		return isset($this->aliases[$alias]) ? $this->aliases[$alias] : $alias;
	}

	/**
	 * Resolve a type hint.
	 *
	 * @access  protected
	 * @param   string     $hint  Type hint
	 * @return  string
	 */

	protected function resolveHint($hint)
	{
		return isset($this->hints[$hint]) ? $this->hints[$hint]['class'] : $hint;
	}

	/**
	 * Merges the provided parameters with the reflection parameters.
	 *
	 * @access  public
	 * @param   array   $reflectionParameters  Reflection parameters
	 * @param   array   $providedParameters    Provided parameters
	 * @return  array
	 */

	protected function mergeParameters(array $reflectionParameters, array $providedParameters)
	{
		// Make the provided parameter array associative

		$associativeProvidedParameters = [];

		foreach($providedParameters as $key => $value)
		{
			if(is_numeric($key))
			{
				$associativeProvidedParameters[$reflectionParameters[$key]->getName()] = $value;
			}
			else
			{
				$associativeProvidedParameters[$key] = $value;
			}
		}

		// Make reflection parameter array associative

		$associativeReflectionParameters = [];

		foreach($reflectionParameters as $key => $value)
		{
			$associativeReflectionParameters[$value->getName()] = $value;
		}

		// Return merged parameters

		return array_replace($associativeReflectionParameters, $associativeProvidedParameters);
	}

	/**
	 * Returns the name of the declaring function.
	 *
	 * @access  protected
	 * @param   \ReflectionParameter  $parameter  ReflectionParameter instance
	 * @return  string
	 */

	protected function getDeclaringFunction(ReflectionParameter $parameter)
	{
		$declaringFunction = $parameter->getDeclaringFunction();

		if($declaringFunction->isClosure())
		{
			return 'Closure';
		}

		return $parameter->getDeclaringClass()->getName() . '::' . $declaringFunction->getName();
	}

	/**
	 * Resolve a parameter.
	 *
	 * @access  protected
	 * @param   \ReflectionParameter  $parameter  ReflectionParameter instance
	 * @return  mixed
	 */

	protected function resolveParameter(ReflectionParameter $parameter)
	{
		if(($parameterClass = $parameter->getClass()) !== null)
		{
			// The parameter should be a class instance. Try to resolve it though the container

			return $this->get($parameterClass->getName());
		}

		if($parameter->isDefaultValueAvailable())
		{
			// The parameter has a default value so we'll use that

			return $parameter->getDefaultValue();
		}

		// We have exhausted all our options. All we can do now is throw an exception

		throw new RuntimeException(vsprintf("%s(): Unable to resolve the [ $%s ] parameter of [ %s ].", [__METHOD__, $parameter->getName(), $this->getDeclaringFunction($parameter)]));
	}

	/**
	 * Resolve parameters.
	 *
	 * @access  public
	 * @param   array   $reflectionParameters  Reflection parameters
	 * @param   array   $providedParameters    Provided Parameters
	 * @return  array
	 */

	protected function resolveParameters(array $reflectionParameters, array $providedParameters)
	{
		if(empty($reflectionParameters))
		{
			return $providedParameters;
		}

		// Merge provided parameters with the ones we got using reflection

		$parameters = $this->mergeParameters($reflectionParameters, $providedParameters);

		// Loop through the parameters and resolve the ones that need resolving

		foreach($parameters as $key => $parameter)
		{
			if($parameter instanceof ReflectionParameter)
			{
				$parameters[$key] = $this->resolveParameter($parameter);
			}
		}

		// Return resolved parameters

		return $parameters;
	}

	/**
	 * Checks if a class is container aware.
	 *
	 * @access  protected
	 * @param   object     $class  Class instance
	 * @return  boolean
	 */

	protected function isContainerAware($class)
	{
		$traits = ClassInspector::getTraits($class);

		return isset($traits['mako\syringe\ContainerAwareTrait']);
	}

	/**
	 * Creates a class instance using a factory closure.
	 *
	 * @access  public
	 * @param   \Closure  $factory     Class name or closure
	 * @param   array     $parameters  Constructor parameters
	 * @return  object
	 */

	protected function closureFactory(Closure $factory, array $parameters)
	{
		// Pass the container as the first parameter followed by the the provided parameters

		$instance = call_user_func_array($factory, array_merge([$this], $parameters));

		// Check that the factory closure returned an object

		if(is_object($instance) === false)
		{
			throw new RuntimeException(vsprintf("%s(): The factory closure must return an object.", [__METHOD__]));
		}

		return $instance;
	}

	/**
	 * Creates a class instance using reflection.
	 *
	 * @access  public
	 * @param   string  $class       Class name or closure
	 * @param   array   $parameters  Constructor parameters
	 * @return  object
	 */

	protected function reflectionFactory($class, array $parameters)
	{
		$class = new ReflectionClass($class);

		// Check that it's possible to instantiate the class

		if(!$class->isInstantiable())
		{
			throw new RuntimeException(vsprintf("%s(): Unable create a [ %s ] instance.", [__METHOD__, $class->getName()]));
		}

		// Get the class constructor

		$constructor = $class->getConstructor();

		if($constructor === null)
		{
			// No constructor has been defined so we'll just return a new instance

			$instance = $class->newInstance();
		}
		else
		{
			// The class has a constructor. Lets get its parameters.

			$constructorParameters = $constructor->getParameters();

			// Create and return a new instance using our resolved parameters

			$instance = $class->newInstanceArgs($this->resolveParameters($constructorParameters, $parameters));
		}

		return $instance;
	}

	/**
	 * Creates a class instance.
	 *
	 * @access  public
	 * @param   string|\Closure  $class       Class name or closure
	 * @param   array            $parameters  Constructor parameters
	 * @return  object
	 */

	protected function factory($class, array $parameters = [])
	{
		// Instantiate class

		if($class instanceof Closure)
		{
			$instance = $this->closureFactory($class, $parameters);
		}
		else
		{
			$instance = $this->reflectionFactory($class, $parameters);
		}

		// Inject container using setter if the class is container aware

		if($this->isContainerAware($instance))
		{
			$instance->setContainer($this);
		}

		// Return the instance

		return $instance;
	}

	/**
	 * Checks if a class is registered in the container.
	 *
	 * @access  public
	 * @param   string   $class  Class name
	 * @return  boolean
	 */

	public function has($class)
	{
		$class = $this->resolveAlias($class);

		return (isset($this->hints[$class]) || isset($this->instances[$class]));
	}

	/**
	 * Returns a class instance.
	 *
	 * @access  public
	 * @param   string   $class           Class name
	 * @param   array    $parameters      Constructor parameters
	 * @param   boolean  $reuseInstance   Reuse existing instance?
	 * @return  object
	 */

	public function get($class, array $parameters = [], $reuseInstance = true)
	{
		$class = $this->resolveAlias($class);

		// If a singleton instance exists then we'll just return it

		if($reuseInstance && isset($this->instances[$class]))
		{
			return $this->instances[$class];
		}

		// Create new instance

		$instance = $this->factory($this->resolveHint($class), $parameters);

		// Store the instance if its registered as a singleton

		if($reuseInstance && isset($this->hints[$class]) && $this->hints[$class]['singleton'])
		{
			$this->instances[$class] = $instance;
		}

		// Return the instance

		return $instance;
	}

	/**
	 * Returns a fresh class instance even if the class is registered as a singleton.
	 *
	 * @access  public
	 * @param   string  $class       Class name
	 * @param   array   $parameters  Constructor parameters
	 * @return  object
	 */

	public function getFresh($class, array $parameters = [])
	{
		return $this->get($class, $parameters, false);
	}

	/**
	 * Execute a callable and inject its dependencies.
	 *
	 * @access  public
	 * @param   callable  $callable    Callable
	 * @param   array     $parameters  Parameters
	 * @return  object
	 */

	public function call(callable $callable, array $parameters = [])
	{
		if($callable instanceof Closure)
		{
			$reflection = new ReflectionFunction($callable);
		}
		else
		{
			$reflection = new ReflectionMethod($callable[0], $callable[1]);
		}

		return call_user_func_array($callable, $this->resolveParameters($reflection->getParameters(), $parameters));
	}
}