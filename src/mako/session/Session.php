<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session;

use LogicException;

use mako\http\Request;
use mako\http\Response;
use mako\security\Comparer;
use mako\session\stores\StoreInterface;
use mako\utility\UUID;

/**
 * Session class.
 *
 * @author  Frederic G. Østby
 */

class Session
{
	/**
	 * Maximum number of tokens stored per session.
	 *
	 * @var int
	 */

	const MAX_TOKENS = 20;

	/**
	 * Has the session been started yet?
	 *
	 * @var boolean
	 */

	protected $started = false;

	/**
	 * Has the session been destroyed?
	 *
	 * @var boolean
	 */

	protected $destroyed = false;

	/**
	 * Request.
	 *
	 * @var \mako\http\Request
	 */

	protected $request;

	/**
	 * Response.
	 *
	 * @var \mako\http\Response
	 */

	protected $response;

	/**
	 * Session store.
	 *
	 * @var \mako\session\stores\StoreInterface
	 */

	protected $store;

	/**
	 * Data TTL in seconds.
	 *
	 * @var int
	 */

	protected $dataTTL = 1800;

	/**
	 * Cookie TTL in seconds.
	 *
	 * @var int
	 */

	protected $cookieTTL = 0;

	/**
	 * Cookie name.
	 *
	 * @var string
	 */

	protected $cookieName = 'mako_session';

	/**
	 * Cookie options.
	 *
	 * @var array
	 */

	protected $cookieOptions =
	[
		'path'     => '/',
		'domain'   => '',
		'secure'   => false,
		'httponly' => false,
	];

	/**
	 * Session id.
	 *
	 * @var string
	 */

	protected $sessionId;

	/**
	 * Session data.
	 *
	 * @var array
	 */

	protected $sessionData = [];

	/**
	 * Flashdata.
	 *
	 * @var array
	 */

	protected $flashData = [];

	/**
	 * Session token.
	 *
	 * @var string
	 */

	protected $token;

	/**
	 * Constructor.
	 *
	 * @access  public
	 * @param   \mako\http\Request                   $request   Request instance
	 * @param   \mako\http\Response                  $response  Response instance
	 * @param   \mako\session\stores\StoreInterface  $store     Session store instance
	 */

	public function __construct(Request $request, Response $response, StoreInterface $store)
	{
		$this->request = $request;

		$this->response = $response;

		$this->store = $store;
	}

	/**
	 * Destructor.
	 *
	 * @access  public
	 */

	public function __destruct()
	{
		// Replace old flash data with new

		$this->sessionData['mako.flashdata'] = $this->flashData;

		// Write session data

		if($this->started && !$this->destroyed)
		{
			$this->store->write($this->sessionId, $this->sessionData, $this->dataTTL);
		}

		// Garbage collection

		if(mt_rand(1, 100) === 100)
		{
			$this->store->gc($this->dataTTL);
		}
	}

	/**
	 * Generates a session id.
	 *
	 * @access  protected
	 * @return  string
	 */

	protected function generateId()
	{
		return hash('sha256', UUID::v4() . uniqid('session', true));
	}

	/**
	 * Adds a session cookie to the response.
	 *
	 * @access  protected
	 */

	protected function setCookie()
	{
		$ttl = $this->cookieTTL === 0 ? 0 : $this->cookieTTL + time();

		$this->response->signedCookie($this->cookieName, $this->sessionId, $ttl, $this->cookieOptions);
	}

	/**
	 * Loads the session data.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function loadData()
	{
		$data = $this->store->read($this->sessionId);

		$this->sessionData = $data === false ? [] : $data;
	}

	/**
	 * Sets the data TTL in seconds.
	 *
	 * @access  public
	 * @param   string  $dataTTL  Cookie name
	 */

	public function setDataTTL($dataTTL)
	{
		if($this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has already been started.", [__METHOD__]));
		}

		$this->dataTTL = max($dataTTL, 0);
	}

	/**
	 * Sets the cookie TTL in seconds.
	 *
	 * @access  public
	 * @param   string  $cookieTTL  Cookie name
	 */

	public function setCookieTTL($cookieTTL)
	{
		if($this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has already been started.", [__METHOD__]));
		}

		$this->cookieTTL = max($cookieTTL, 0);
	}

	/**
	 * Sets the cookie name.
	 *
	 * @access  public
	 * @param   string  $cookieName  Cookie name
	 */

	public function setCookieName($cookieName)
	{
		if($this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has already been started.", [__METHOD__]));
		}

		$this->cookieName = $cookieName;
	}

	/**
	 * Sets cookie options.
	 *
	 * @access  public
	 * @param   array  $cookieOptions  Cookie options
	 */

	public function setCookieOptions(array $cookieOptions)
	{
		if($this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has already been started.", [__METHOD__]));
		}

		$this->cookieOptions = $cookieOptions;
	}

	/**
	 * Starts the session.
	 *
	 * @access  public
	 */

	public function start()
	{
		if($this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has already been started.", [__METHOD__]));
		}

		// Set the started flag to true

		$this->started = true;

		// Get the session id from the cookie or generate a new one if it doesn't exist.

		$this->sessionId = $this->request->signedCookie($this->cookieName, false);

		if($this->sessionId === false)
		{
			$this->sessionId = $this->generateId();
		}

		// Create a new / update the existing session cookie

		$this->setCookie();

		// Load the session data

		$this->loadData();

		// Create a session token if we don't have one

		if(empty($this->sessionData['mako.token']))
		{
			$this->sessionData['mako.token'] = $this->generateId();
		}

		$this->token = $this->sessionData['mako.token'];
	}

	/**
	 * Returns the session id.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getId()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return $this->sessionId;
	}

	/**
	 * Regenerate the session id and returns it.
	 *
	 * @access  public
	 * @param   boolean  $keepOld  Keep the session data associated with the old session id?
	 * @return  string
	 */

	public function regenerateId($keepOld = false)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		// Delete old data if we don't want to keep it

		if(!$keepOld)
		{
			$this->store->delete($this->sessionId);
		}

		// Generate a new id and set a new cookie

		$this->sessionId = $this->generateId();

		$this->setCookie();

		// Return the new session id

		return $this->sessionId;
	}

	/**
	 * Returns all the seesion data.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getData()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return $this->sessionData;
	}

	/**
	 * Store a value in the session.
	 *
	 * @access  public
	 * @param   string  $key    Session key
	 * @param   mixed   $value  Session data
	 */

	public function put($key, $value)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$this->sessionData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $key  Session key
	 * @return  boolean
	 */

	public function has($key)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return isset($this->sessionData[$key]);
	}

	/**
	 * Returns a value from the session.
	 *
	 * @access  public
	 * @param   string  $key      Session key
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	public function get($key, $default = null)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return isset($this->sessionData[$key]) ? $this->sessionData[$key] : $default;
	}

	/**
	 * Removes a value from the session.
	 *
	 * @access  public
	 * @param   string  $key  Session key
	 */

	public function remove($key)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		unset($this->sessionData[$key]);
	}

	/**
	 * Store a flash value in the session.
	 *
	 * @access  public
	 * @param   string  $key    Flash key
	 * @param   mixed   $value  Flash data
	 * @return  mixed
	 */

	public function putFlash($key, $value)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$this->flashData[$key] = $value;
	}

	/**
	 * Returns TRUE if key exists in the session and FALSE if not.
	 *
	 * @access  public
	 * @param   string   $key  Session key
	 * @return  boolean
	 */

	public function hasFlash($key)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return isset($this->sessionData['mako.flashdata'][$key]);
	}

	/**
	 * Returns a flash value from the session.
	 *
	 * @access  public
	 * @param   string  $key      Session key
	 * @param   mixed   $default  Default value
	 * @return  mixed
	 */

	public function getFlash($key, $default = null)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return isset($this->sessionData['mako.flashdata'][$key]) ? $this->sessionData['mako.flashdata'][$key] : $default;
	}

	/**
	 * Removes a value from the session.
	 *
	 * @access  public
	 * @param   string  $key  Session key
	 */

	public function removeFlash($key)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		unset($this->sessionData['mako.flashdata'][$key]);
	}

	/**
	 * Extends the lifetime of the flash data by one request.
	 *
	 * @access  public
	 * @param   array   $keys  Keys to preserve
	 */

	public function reflash(array $keys = [])
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$flashData = isset($this->sessionData['mako.flashdata']) ? $this->sessionData['mako.flashdata'] : [];

		$flashData = empty($keys) ? $flashData : array_intersect_key($flashData, array_flip($keys));

		$this->flashData = array_merge($this->flashData, $flashData);
	}

	/**
	 * Returns the session token.
	 *
	 * @access  public
	 * @return  string
	 */

	public function getToken()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return $this->token;
	}

	/**
	 * Generates a new session token and returns it.
	 *
	 * @access  public
	 * @return  string
	 */

	public function regenerateToken()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return $this->token = $this->sessionData['mako.token'] = $this->generateId();
	}

	/**
	 * Validates the provided token.
	 *
	 * @access  public
	 * @param   string   $token  Token to validate
	 * @return  boolean
	 */

	public function validateToken($token)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		return Comparer::compare($this->token, $token);
	}

	/**
	 * Returns random security token.
	 *
	 * @access  public
	 * @return  string
	 */

	public function generateOneTimeToken()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		if(!empty($this->sessionData['mako.tokens']))
		{
			$this->sessionData['mako.tokens'] = array_slice($this->sessionData['mako.tokens'], 0, (static::MAX_TOKENS - 1));
		}
		else
		{
			$this->sessionData['mako.tokens'] = [];
		}

		$token = $this->generateId();

		array_unshift($this->sessionData['mako.tokens'], $token);

		return $token;
	}

	/**
	 * Validates security token.
	 *
	 * @access  public
	 * @param   string   $token  Security token
	 * @return  boolean
	 */

	public function validateOneTimeToken($token)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		if(!empty($this->sessionData['mako.tokens']))
		{
			foreach($this->sessionData['mako.tokens'] as $key => $value)
			{
				if(Comparer::compare($value, $token))
				{
					unset($this->sessionData['mako.tokens'][$key]);

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Clears all session data.
	 *
	 * @access  public
	 */

	public function clear()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$this->sessionData = [];
	}

	/**
	 * Destroys the session.
	 *
	 * @access  public
	 */

	public function destroy()
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$this->store->delete($this->sessionId);

		$this->response->deleteCookie($this->cookieName, $this->cookieOptions);

		$this->destroyed = true;
	}
}