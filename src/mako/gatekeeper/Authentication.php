<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper;

use Closure;

use mako\gatekeeper\adapters\AdapterInterface;

/**
 * Authentication.
 *
 * @author Frederic G. Østby
 *
 * @method string                                                     getName()
 * @method void                                                       setUserRepository(\mako\gatekeeper\repositories\user\UserRepositoryInterface $userRepository)
 * @method \mako\gatekeeper\repositories\user\UserRepositoryInterface getUserRepository()
 * @method void                                                       setUser(\mako\gatekeeper\entities\user\UserEntityInterface $user)
 * @method null|\mako\gatekeeper\entities\user\UserEntityInterface    getUser()
 * @method bool                                                       isGuest()
 * @method bool                                                       isLoggedIn()
 * @method bool                                                       basicAuth()
 */
class Authentication
{
	/**
	 * Status code for banned users.
	 *
	 * @var int
	 */
	const LOGIN_BANNED = 100;

	/**
	 * Status code for users who need to activate their account.
	 *
	 * @var int
	 */
	const LOGIN_ACTIVATING = 101;

	/**
	 * Status code for users who fail to provide the correct credentials.
	 *
	 * @var int
	 */
	const LOGIN_INCORRECT = 102;

	/**
	 * Status code for users that are temporarily locked.
	 *
	 * @var int
	 */
	const LOGIN_LOCKED = 103;

	/**
	 * Default adapter name.
	 *
	 * @var string
	 */
	protected $defaultAdapter;

	/**
	 * Adapters.
	 *
	 * @var array
	 */
	protected $adapters = [];

	/**
	 * Adapter factories.
	 *
	 * @var array
	 */
	protected $adapterFactories = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @param string|\mako\gatekeeper\adapters\AdapterInterface $adapter Adapter name or adapter instance
	 * @param null|\Closure                                     $factory Adapter factory
	 */
	public function __construct($adapter, Closure $factory = null)
	{
		$this->registerAdapter($adapter, $factory, true);
	}

	/**
	 * Registers a adapter.
	 *
	 * @access public
	 * @param string|\mako\gatekeeper\adapters\AdapterInterface $adapter     Adapter name or adapter instance
	 * @param null|\Closure                                     $factory     Adapter factory
	 * @param bool                                              $makeDefault Make it the default adapter?
	 */
	protected function registerAdapter($adapter, Closure $factory = null, bool $makeDefault = false)
	{
		if($adapter instanceof AdapterInterface)
		{
			$this->adapters[$name = $adapter->getName()] = $adapter;
		}
		else
		{
			$this->adapterFactories[$name = $adapter] = $factory;
		}

		if($makeDefault)
		{
			$this->defaultAdapter = $name;
		}
	}

	/**
	 * Registers a new adapter.
	 *
	 * @access public
	 * @param  string|\mako\gatekeeper\adapters\AdapterInterface $adapter Adapter name or adapter instance
	 * @param  null|\Closure                                     $factory Adapter factory
	 * @return \mako\gatekeeper\Authentication
	 */
	public function extend($adapter, Closure $factory = null): Authentication
	{
		$this->registerAdapter($adapter, $factory);

		return $this;
	}

	/**
	 * Sets the defaut adapter name.
	 *
	 * @access public
	 * @param  string                          $name Adapter name
	 * @return \mako\gatekeeper\Authentication
	 */
	public function useAsDefaultAdapter(string $name): Authentication
	{
		$this->defaultAdapter = $name;

		return $this;
	}

	/**
	 * Resolves a adapter instance.
	 *
	 * @access protected
	 * @param  string                                    $name Adapter name
	 * @return \mako\gatekeeper\adapters\AdapterInterace
	 */
	protected function resolveAdapter(string $name): AdapterInterface
	{
		$factory = $this->adapterFactories[$name];

		return $this->adapters[$name] = $factory();
	}

	/**
	 * Returns a adapter instance.
	 *
	 * @access public
	 * @param  null|string                               $name Adapter name
	 * @return \mako\gatekeeper\adapters\AdapterInterace
	 */
	public function adapter(string $name = null): AdapterInterface
	{
		$name = $name ?: $this->defaultAdapter;

		return $this->adapters[$name] ?? $this->resolveAdapter($name);
	}

	/**
	 * Magic shortcut to the default adapter.
	 *
	 * @access public
	 * @param  string $method    Method name
	 * @param  array  $arguments Method arguments
	 * @return mixed
	 */
	public function __call(string $method, $arguments)
	{
		return $this->adapter()->$method(...$arguments);
	}
}
