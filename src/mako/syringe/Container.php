<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\syringe;

use Closure;
use mako\classes\ClassInspector;
use mako\syringe\attributes\InjectorInterface;
use mako\syringe\exceptions\ContainerException;
use mako\syringe\exceptions\UnableToInstantiateException;
use mako\syringe\exceptions\UnableToResolveParameterException;
use mako\syringe\traits\ContainerAwareTrait;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

use function array_filter;
use function array_keys;
use function array_replace;
use function array_values;
use function count;
use function is_array;
use function is_int;
use function is_object;
use function sprintf;

/**
 * Dependecy injection container.
 */
class Container
{
	/**
	 * Registered type hints.
	 */
	protected array $hints = [];

	/**
	 * Aliases.
	 */
	protected array $aliases = [];

	/**
	 * Singleton instances.
	 */
	protected array $instances = [];

	/**
	 * Contextual dependencies.
	 */
	protected array $contextualDependencies = [];

	/**
	 * Instance replacers.
	 */
	protected array $replacers = [];

	/**
	 * Parse the hint parameter.
	 */
	protected function parseHint(array|string $hint): string
	{
		if (is_array($hint)) {
			[$hint, $alias] = $hint;

			$this->aliases[$alias] = $hint;
		}

		return $hint;
	}

	/**
	 * Register a type hint.
	 */
	public function register(array|string $hint, Closure|string $class, bool $singleton = false): void
	{
		$this->hints[$this->parseHint($hint)] = ['class' => $class, 'singleton' => $singleton];
	}

	/**
	 * Register a type hint and return the same instance every time.
	 */
	public function registerSingleton(array|string $hint, Closure|string $class): void
	{
		$this->register($hint, $class, true);
	}

	/**
	 * Register a singleton instance.
	 */
	public function registerInstance(array|string $hint, object $instance): void
	{
		$this->instances[$this->parseHint($hint)] = $instance;
	}

	/**
	 * Registers a contextual dependency.
	 */
	public function registerContextualDependency(array|string $dependent, string $interface, string $implementation): void
	{
		if (is_array($dependent)) {
			$dependent = "{$dependent[0]}::{$dependent[1]}";
		}

		$this->contextualDependencies[$dependent][$interface] = $implementation;
	}

	/**
	 * Return the name based on its alias. If no alias exists then we'll just return the value we received.
	 */
	protected function resolveAlias(string $alias): string
	{
		return $this->aliases[$alias] ?? $alias;
	}

	/**
	 * Replaces previously resolved instances.
	 */
	protected function replaceInstances(string $hint): void
	{
		if (isset($this->replacers[$hint])) {
			$instance = $this->get($hint);

			foreach ($this->replacers[$hint] as $replacer) {
				$replacer($instance);
			}
		}
	}

	/**
	 * Registers replacers.
	 */
	public function onReplace(string $hint, callable $replacer, ?string $eventName = null): void
	{
		$hint = $this->resolveAlias($hint);

		$eventName === null ? ($this->replacers[$hint][] = $replacer) : ($this->replacers[$hint][$eventName] = $replacer);
	}

	/**
	 * Replaces a registered type hint.
	 */
	public function replace(string $hint, Closure|string $class, bool $singleton = false): void
	{
		$hint = $this->resolveAlias($hint);

		if (!isset($this->hints[$hint])) {
			throw new ContainerException(sprintf('Unable to replace [ %s ] as it hasn\'t been registered.', $hint));
		}

		$this->hints[$hint]['class'] = $class;

		if ($singleton) {
			unset($this->instances[$hint]);
		}

		$this->replaceInstances($hint);
	}

	/**
	 * Replaces a registered singleton type hint.
	 */
	public function replaceSingleton(string $hint, Closure|string $class): void
	{
		$this->replace($hint, $class, true);
	}

	/**
	 * Replaces a singleton instance.
	 */
	public function replaceInstance(string $hint, object $instance): void
	{
		$hint = $this->resolveAlias($hint);

		if (!isset($this->instances[$hint])) {
			throw new ContainerException(sprintf('Unable to replace [ %s ] as it hasn\'t been registered.', $hint));
		}

		$this->instances[$hint] = $instance;

		$this->replaceInstances($hint);
	}

	/**
	 * Resolves a type hint.
	 */
	protected function resolveHint(string $hint): Closure|string
	{
		return $this->hints[$hint]['class'] ?? $hint;
	}

	/**
	 * Resolves a contextual dependency.
	 */
	protected function resolveContextualDependency(string $dependent, string $interface): string
	{
		return $this->contextualDependencies[$dependent][$interface] ?? $interface;
	}

	/**
	 * Merges the provided parameters with the reflection parameters.
	 */
	protected function mergeParameters(array $reflectionParameters, array $providedParameters): array
	{
		// Make reflection parameter array associative

		$associativeReflectionParameters = [];

		foreach ($reflectionParameters as $value) {
			$associativeReflectionParameters[$value->getName()] = $value;
		}

		// Make the provided parameter array associative

		$associativeProvidedParameters = [];

		foreach ($providedParameters as $key => $value) {
			$associativeProvidedParameters[is_int($key) ? $reflectionParameters[$key]->getName() : $key] = $value;
		}

		// Return merged parameters

		return array_replace($associativeReflectionParameters, $associativeProvidedParameters);
	}

	/**
	 * Returns the name of the declaring function.
	 */
	protected function getDeclaringFunction(ReflectionParameter $parameter): string
	{
		$declaringFunction = $parameter->getDeclaringFunction();

		if ($declaringFunction->isClosure()) {
			return 'Closure';
		}

		if (($class = $parameter->getDeclaringClass()) === null) {
			return $declaringFunction->getName();
		}

		return "{$class->getName()}::{$declaringFunction->getName()}";
	}

	/**
	 * Resolve a parameter.
	 */
	protected function resolveParameter(ReflectionParameter $parameter, ?ReflectionClass $class = null, ?string $method = null): mixed
	{
		// If the parameter has a injector attribute then we'll use that to resolve the value

		if (!empty($attributes = $parameter->getAttributes(InjectorInterface::class, ReflectionAttribute::IS_INSTANCEOF))) {
			/** @var InjectorInterface $injector */
			$injector = $this->get($attributes[0]->getName(), $attributes[0]->getArguments());

			return $injector->getParameterValue();
		}

		// Continue with normal parameter resolving

		$parameterType = $parameter->getType();

		// If the parameter should be a class instance then we'll try to resolve it using the container

		if ($parameterType !== null) {
			$parameterClassName = null;

			if ($parameterType instanceof ReflectionNamedType) {
				if (!$parameterType->isBuiltin()) {
					$parameterClassName = $parameterType->getName();
				}
			}
			else {
				// Checking if we have a intersection type

				if ($parameterType instanceof ReflectionIntersectionType) {
					$parameterClassName = (string) $parameterType;
				}

				// A nullable intersection type will be detected as a union type so we'll have to dig a bit deeper

				elseif ($parameterType instanceof ReflectionUnionType && $parameterType->allowsNull()) {
					$intersection = array_filter($parameterType->getTypes(), static fn ($type) => $type instanceof ReflectionIntersectionType);

					if (count($intersection) === 1) {
						$parameterClassName = (string) $intersection[0];
					}
				}

				// If the intersection type isn't registered in the container
				// then we'll just set the classname back to null

				if ($parameterClassName !== null && !$this->has($parameterClassName)) {
					$parameterClassName = null;
				}
			}

			// If we ended up with a parameter class name then we'll try to resolve it using the container

			if ($parameterClassName !== null) {
				if ($class !== null) {
					$parameterClassName = $this->resolveContextualDependency(($method === null ? $class->getName() : "{$class->getName()}::{$method}"), $parameterClassName);
				}

				try {
					return $this->get($parameterClassName);
				}
				catch (UnableToInstantiateException|UnableToResolveParameterException $e) {
					if ($parameter->allowsNull()) {
						return null;
					}

					throw $e;
				}
			}
		}

		// If the parameter has a default value then we'll use that

		if ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}

		// The parameter is nullable so we'll just return null

		if ($parameterType !== null && $parameter->allowsNull()) {
			return null;
		}

		// We have exhausted all our options. All we can do now is throw an exception

		throw new UnableToResolveParameterException(sprintf('Unable to resolve the [ $%s ] parameter of [ %s ].', $parameter->getName(), $this->getDeclaringFunction($parameter)));
	}

	/**
	 * Resolve parameters.
	 */
	protected function resolveParameters(array $reflectionParameters, array $providedParameters, ?ReflectionClass $class = null, ?string $method = null): array
	{
		if (empty($reflectionParameters)) {
			return array_values($providedParameters);
		}

		// Merge provided parameters with the ones we got using reflection

		$parameters = $this->mergeParameters($reflectionParameters, $providedParameters);

		// Loop through the parameters and resolve the ones that need resolving

		foreach ($parameters as $key => $parameter) {
			if ($parameter instanceof ReflectionParameter) {
				$parameters[$key] = $this->resolveParameter($parameter, $class, $method);
			}
		}

		// Return resolved parameters

		return array_values($parameters);
	}

	/**
	 * Checks if a class is container aware.
	 */
	protected function isContainerAware(object $class): bool
	{
		$traits = ClassInspector::getTraits($class);

		return isset($traits[ContainerAwareTrait::class]);
	}

	/**
	 * Creates a class instance using a factory closure.
	 */
	protected function closureFactory(Closure $factory, array $parameters): object
	{
		// Pass the container as the first parameter followed by the the provided parameters

		return $factory(...[$this, ...$parameters]);
	}

	/**
	 * Creates a class instance using reflection.
	 */
	protected function reflectionFactory(string $class, array $parameters): object
	{
		$class = new ReflectionClass($class);

		// Check that it's possible to instantiate the class

		if (!$class->isInstantiable()) {
			throw new UnableToInstantiateException(sprintf('Unable to create a [ %s ] instance.', $class->getName()));
		}

		// Get the class constructor

		$constructor = $class->getConstructor();

		// If we don't have a constructor then we'll just return a new instance

		if ($constructor === null) {
			return $class->newInstance();
		}

		// The class had a constructor so we'll return a new instance using our resolved parameters

		return $class->newInstanceArgs($this->resolveParameters($constructor->getParameters(), $parameters, $class));
	}

	/**
	 * Creates a class instance.
	 */
	public function factory(Closure|string $class, array $parameters = []): object
	{
		// Instantiate class

		if ($class instanceof Closure) {
			$instance = $this->closureFactory($class, $parameters);
		}
		else {
			$instance = $this->reflectionFactory($class, $parameters);
		}

		// Inject container using setter if the class is container aware

		if ($this->isContainerAware($instance)) {
			$instance->setContainer($this);
		}

		// Return the instance

		return $instance;
	}

	/**
	 * Returns TRUE if the class is registered in the container and FALSE if not.
	 */
	public function has(string $class): bool
	{
		$class = $this->resolveAlias($class);

		return isset($this->hints[$class]) || isset($this->instances[$class]);
	}

	/**
	 * Returns TRUE if there's an instance of the class in the container and FALSE if not.
	 */
	public function hasInstanceOf(string $class): bool
	{
		return isset($this->instances[$this->resolveAlias($class)]);
	}

	/**
	 * Returns the class names of the instances that have been registered in the container.
	 */
	public function getInstanceClassNames(): array
	{
		return array_keys($this->instances);
	}

	/**
	 * Removes a class instance from the container.
	 */
	public function removeInstance(string $class): void
	{
		unset($this->instances[$this->resolveAlias($class)]);
	}

	/**
	 * Returns TRUE if a class has been registered as a singleton and FALSE if not.
	 */
	public function isSingleton(string $class): bool
	{
		$class = $this->resolveAlias($class);

		return isset($this->instances[$class]) || (isset($this->hints[$class]) && $this->hints[$class]['singleton'] === true);
	}

	/**
	 * Returns a class instance.
	 *
	 * @template T of object
	 * @param  class-string<T> $class
	 * @return T
	 */
	public function get(string $class, array $parameters = [], bool $reuseInstance = true): object
	{
		$class = $this->resolveAlias($class);

		// If a singleton instance exists then we'll just return it

		if ($reuseInstance && isset($this->instances[$class])) {
			return $this->instances[$class];
		}

		// Create new instance

		$instance = $this->factory($this->resolveHint($class), $parameters);

		// Store the instance if it's registered as a singleton

		if ($reuseInstance && isset($this->hints[$class]) && $this->hints[$class]['singleton']) {
			$this->instances[$class] = $instance;
		}

		// Return the instance

		return $instance;
	}

	/**
	 * Returns a fresh class instance even if the class is registered as a singleton.
	 *
	 * @template T of object
	 * @param  class-string<T> $class
	 * @return T
	 */
	public function getFresh(string $class, array $parameters = []): object
	{
		return $this->get($class, $parameters, false);
	}

	/**
	 * Execute a callable and inject its dependencies.
	 */
	public function call(callable $callable, array $parameters = []): mixed
	{
		if (is_object($callable) && ($callable instanceof Closure) === false) {
			$callable = [$callable, '__invoke'];
		}

		if (is_array($callable)) {
			$reflection = new ReflectionMethod($callable[0], $callable[1]);

			return $callable(...$this->resolveParameters($reflection->getParameters(), $parameters, $reflection->getDeclaringClass(), $callable[1]));
		}

		return $callable(...$this->resolveParameters((new ReflectionFunction($callable))->getParameters(), $parameters));
	}
}
