<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

use mako\syringe\ClassInspector;
use mako\syringe\traits\ContainerAwareTrait;

/**
 * Inversion of control container.
 *
 * @author Frederic G. Østby
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
	 * Contextual dependencies.
	 *
	 * @var array
	 */
	protected $contextualDependencies = [];

	/**
	 * Instance replacers.
	 *
	 * @var array
	 */
	protected $replacers = [];

	/**
	 * Parse the hint parameter.
	 *
	 * @param  string|array $hint Type hint or array contaning both type hint and alias
	 * @return string
	 */
	protected function parseHint($hint): string
	{
		if(is_array($hint))
		{
			list($hint, $alias) = $hint;

			$this->aliases[$alias] = $hint;
		}

		return $hint;
	}

	/**
	 * Register a type hint.
	 *
	 * @param string|array    $hint      Type hint or array contaning both type hint and alias
	 * @param string|\Closure $class     Class name or closure
	 * @param bool            $singleton Should we return the same instance every time?
	 */
	public function register($hint, $class, bool $singleton = false)
	{
		$this->hints[$this->parseHint($hint)] = ['class' => $class, 'singleton' => $singleton];
	}

	/**
	 * Register a type hint and return the same instance every time.
	 *
	 * @param string|array    $hint  Type hint or array contaning both type hint and alias
	 * @param string|\Closure $class Class name or closure
	 */
	public function registerSingleton($hint, $class)
	{
		$this->register($hint, $class, true);
	}

	/**
	 * Register a singleton instance.
	 *
	 * @param string|array $hint     Type hint or array contaning both type hint and alias
	 * @param object       $instance Class instance
	 */
	public function registerInstance($hint, $instance)
	{
		$this->instances[$this->parseHint($hint)] = $instance;
	}

	/**
	 * Registers a contextual dependency.
	 *
	 * @param string $class          Class
	 * @param string $interface      Interface
	 * @param string $implementation Implementation
	 */
	public function registerContextualDependency(string $class, string $interface, string $implementation)
	{
		$this->contextualDependencies[$class][$interface] = $implementation;
	}

	/**
	 * Return the name based on its alias. If no alias exists then we'll just return the value we received.
	 *
	 * @param  string $alias Alias
	 * @return string
	 */
	protected function resolveAlias(string $alias): string
	{
		return $this->aliases[$alias] ?? $alias;
	}

	/**
	 * Replaces previously resolved instances.
	 *
	 * @param string $hint Type hint
	 */
	protected function replaceInstances($hint)
	{
		if(isset($this->replacers[$hint]))
		{
			$instance = $this->get($hint);

			foreach($this->replacers[$hint] as $replacer)
			{
				$replacer($instance);
			}
		}
	}

	/**
	 * Registers replacers.
	 *
	 * @param string      $hint      Type hint
	 * @param callable    $replacer  Instance replacer
	 * @param string|null $eventName Event name
	 */
	public function onReplace($hint, callable $replacer, string $eventName = null)
	{
		$hint = $this->resolveAlias($hint);

		$eventName === null ? ($this->replacers[$hint][] = $replacer) : ($this->replacers[$hint][$eventName] = $replacer);
	}

	/**
	 * Replaces a registered type hint.
	 *
	 * @param string          $hint      Type hint
	 * @param string|\Closure $class     Class name or closure
	 * @param bool            $singleton Are we replacing a singleton?
	 */
	public function replace(string $hint, $class, bool $singleton = false)
	{
		$hint = $this->resolveAlias($hint);

		if(!isset($this->hints[$hint]))
		{
			throw new RuntimeException(vsprintf('Unable to replace [ %s ] as it hasn\'t been registered.', [$hint]));
		}

		$this->hints[$hint]['class'] = $class;

		if($singleton)
		{
			unset($this->instances[$hint]);
		}

		$this->replaceInstances($hint);
	}

	/**
	 * Replaces a registered singleton type hint.
	 *
	 * @param string          $hint  Type hint
	 * @param string|\Closure $class Class name or closure
	 */
	public function replaceSingleton(string $hint, $class)
	{
		$this->replace($hint, $class, true);
	}

	/**
	 * Replaces a singleton instance.
	 *
	 * @param string|array $hint     Type hint or array contaning both type hint and alias
	 * @param object       $instance Class instance
	 */
	public function replaceInstance(string $hint, $instance)
	{
		$hint = $this->resolveAlias($hint);

		if(!isset($this->instances[$hint]))
		{
			throw new RuntimeException(vsprintf('Unable to replace [ %s ] as it hasn\'t been registered.', [$hint]));
		}

		$this->instances[$hint] = $instance;

		$this->replaceInstances($hint);
	}

	/**
	 * Resolves a type hint.
	 *
	 * @param  string          $hint Type hint
	 * @return string|\Closure
	 */
	protected function resolveHint(string $hint)
	{
		return $this->hints[$hint]['class'] ?? $hint;
	}

	/**
	 * Resolves a contextual dependency.
	 *
	 * @param  string $class     Class
	 * @param  string $interface Interface
	 * @return string
	 */
	protected function resolveContextualDependency(string $class, string $interface): string
	{
		return $this->contextualDependencies[$class][$interface] ?? $interface;
	}

	/**
	 * Merges the provided parameters with the reflection parameters.
	 *
	 * @param  array $reflectionParameters Reflection parameters
	 * @param  array $providedParameters   Provided parameters
	 * @return array
	 */
	protected function mergeParameters(array $reflectionParameters, array $providedParameters): array
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

		foreach($reflectionParameters as $value)
		{
			$associativeReflectionParameters[$value->getName()] = $value;
		}

		// Return merged parameters

		return array_replace($associativeReflectionParameters, $associativeProvidedParameters);
	}

	/**
	 * Returns the name of the declaring function.
	 *
	 * @param  \ReflectionParameter $parameter ReflectionParameter instance
	 * @return string
	 */
	protected function getDeclaringFunction(ReflectionParameter $parameter): string
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
	 * @param  \ReflectionParameter  $parameter ReflectionParameter instance
	 * @param  \ReflectionClass|null $class     ReflectionClass instance
	 * @return mixed
	 */
	protected function resolveParameter(ReflectionParameter $parameter, ReflectionClass $class = null)
	{
		if(($parameterClass = $parameter->getClass()) !== null)
		{
			// The parameter should be a class instance. Try to resolve it though the container

			$parameterClassName = $parameterClass->getName();

			if($class !== null)
			{
				$parameterClassName = $this->resolveContextualDependency($class->getName(), $parameterClassName);
			}

			return $this->get($parameterClassName);
		}

		if($parameter->isDefaultValueAvailable())
		{
			// The parameter has a default value so we'll use that

			return $parameter->getDefaultValue();
		}

		// We have exhausted all our options. All we can do now is throw an exception

		throw new RuntimeException(vsprintf('Unable to resolve the [ $%s ] parameter of [ %s ].', [$parameter->getName(), $this->getDeclaringFunction($parameter)]));
	}

	/**
	 * Resolve parameters.
	 *
	 * @param  array                 $reflectionParameters Reflection parameters
	 * @param  array                 $providedParameters   Provided Parameters
	 * @param  \ReflectionClass|null $class                ReflectionClass instance
	 * @return array
	 */
	protected function resolveParameters(array $reflectionParameters, array $providedParameters, ReflectionClass $class = null): array
	{
		if(empty($reflectionParameters))
		{
			return array_values($providedParameters);
		}

		// Merge provided parameters with the ones we got using reflection

		$parameters = $this->mergeParameters($reflectionParameters, $providedParameters);

		// Loop through the parameters and resolve the ones that need resolving

		foreach($parameters as $key => $parameter)
		{
			if($parameter instanceof ReflectionParameter)
			{
				$parameters[$key] = $this->resolveParameter($parameter, $class);
			}
		}

		// Return resolved parameters

		return array_values($parameters);
	}

	/**
	 * Checks if a class is container aware.
	 *
	 * @param  object $class Class instance
	 * @return bool
	 */
	protected function isContainerAware($class): bool
	{
		$traits = ClassInspector::getTraits($class);

		return isset($traits[ContainerAwareTrait::class]);
	}

	/**
	 * Creates a class instance using a factory closure.
	 *
	 * @param  \Closure $factory    Class name or closure
	 * @param  array    $parameters Constructor parameters
	 * @return object
	 */
	protected function closureFactory(Closure $factory, array $parameters)
	{
		// Pass the container as the first parameter followed by the the provided parameters

		$instance = $factory(...array_merge([$this], $parameters));

		// Check that the factory closure returned an object

		if(is_object($instance) === false)
		{
			throw new RuntimeException('The factory closure must return an object.');
		}

		return $instance;
	}

	/**
	 * Creates a class instance using reflection.
	 *
	 * @param  string $class      Class name
	 * @param  array  $parameters Constructor parameters
	 * @return object
	 */
	protected function reflectionFactory(string $class, array $parameters)
	{
		$class = new ReflectionClass($class);

		// Check that it's possible to instantiate the class

		if(!$class->isInstantiable())
		{
			throw new RuntimeException(vsprintf('Unable to create a [ %s ] instance.', [$class->getName()]));
		}

		// Get the class constructor

		$constructor = $class->getConstructor();

		if($constructor === null)
		{
			// No constructor has been defined so we'll just return a new instance

			return $class->newInstance();
		}

		// The class has a constructor. Lets get its parameters.

		$constructorParameters = $constructor->getParameters();

		// Create and return a new instance using our resolved parameters

		return $class->newInstanceArgs($this->resolveParameters($constructorParameters, $parameters, $class));
	}

	/**
	 * Creates a class instance.
	 *
	 * @param  string|\Closure $class      Class name or closure
	 * @param  array           $parameters Constructor parameters
	 * @return object
	 */
	public function factory($class, array $parameters = [])
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
	 * @param  string $class Class name
	 * @return bool
	 */
	public function has(string $class): bool
	{
		$class = $this->resolveAlias($class);

		return (isset($this->hints[$class]) || isset($this->instances[$class]));
	}

	/**
	 * Returns TRUE if a class has been registered as a singleton and FALSE if not.
	 *
	 * @param  string $class Class name
	 * @return bool
	 */
	public function isSingleton(string $class): bool
	{
		$class = $this->resolveAlias($class);

		return isset($this->instances[$class]) || (isset($this->hints[$class]) && $this->hints[$class]['singleton'] === true);
	}

	/**
	 * Returns a class instance.
	 *
	 * @param  string $class         Class name
	 * @param  array  $parameters    Constructor parameters
	 * @param  bool   $reuseInstance Reuse existing instance?
	 * @return object
	 */
	public function get(string $class, array $parameters = [], bool $reuseInstance = true)
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
	 * @param  string $class      Class name
	 * @param  array  $parameters Constructor parameters
	 * @return object
	 */
	public function getFresh(string $class, array $parameters = [])
	{
		return $this->get($class, $parameters, false);
	}

	/**
	 * Execute a callable and inject its dependencies.
	 *
	 * @param  callable $callable   Callable
	 * @param  array    $parameters Parameters
	 * @return object
	 */
	public function call(callable $callable, array $parameters = [])
	{
		if(is_array($callable))
		{
			$reflection = new ReflectionMethod($callable[0], $callable[1]);
		}
		else
		{
			$reflection = new ReflectionFunction($callable);
		}

		return $callable(...$this->resolveParameters($reflection->getParameters(), $parameters));
	}
}
