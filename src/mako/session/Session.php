<?php

namespace mako\session;

use \LogicException;

use \mako\http\Request;
use \mako\http\Response;
use \mako\session\store\StoreInterface;
use \mako\utility\UUID;

/**
 * Session class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Session
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

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
	 * @var \mako\session\store\StoreInterface
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

	protected $cookieOptions = [];

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

	protected $sessionData;

	/**
	 * Flashdata.
	 * 
	 * @var array
	 */

	protected $flashData;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\http\Request                  $request   Request instance
	 * @param   \mako\http\Response                 $response  Response instance
	 * @param   \mako\session\store\StoreInterface  $store     Session store instance
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

		$this->sessionData['mako:flashdata'] = $this->flashData;

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

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Generates a session id.
	 * 
	 * @access  protected
	 * @return  string
	 */

	protected function generateId()
	{
		return md5(UUID::v4());
	}

	/**
	 * Adds a session cookie to the response.
	 * 
	 * @access  protected
	 * @param   string     $value  (optional) Cookie value
	 * @param   int        $ttl    (optional) Cookie ttl
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
	 * @param   string  $cookieName  Cookie name
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
	 * @param   string  $cookieName  Cookie name
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

		// Get the session id from the cookie or generate a new one if it doesn't exist.

		$this->sessionId = $this->request->signedCookie($this->cookieName, $this->generateId());

		// Create a new / update the existing session cookie

		$this->setCookie();

		// Load the session data

		$this->loadData();

		// Set the started flag to true

		$this->started = true;
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
	 * @param   boolean  $keepOld  (optional) Keep the session data associated with the old session id?
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

	public function remember($key, $value)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$this->sessionData[$key] = $value;
	}

	/**
	 * Sets or gets flash data from the session.
	 *
	 * @access  public
	 * @param   string  $key   Session flash key
	 * @param   mixed   $data  (optional) Flash data
	 * @return  mixed
	 */

	public function flash($key, $data = null)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		if($data === null)
		{
			return isset($this->sessionData['mako:flashdata'][$key]) ? $this->sessionData['mako:flashdata'][$key] : false;
		}
		else
		{
			$this->flashData[$key] = $data;
		}
	}

	/**
	 * Extends the lifetime of the flash data by one request.
	 * 
	 * @access  public
	 * @param   array   $keys  (optional) Keys to preserve
	 */

	public function reflash(array $keys = [])
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		$flashData = empty($keys) ? $this->sessionData['mako:flashdata'] : array_intersect_key($this->sessionData['mako:flashdata'], array_flip($keys));

		$this->flashData = array_merge($this->flashData, $flashData);
	}

	/**
	 * Removes a value from the session.
	 * 
	 * @access  public
	 * @param   string  $key  Session key
	 */

	public function forget($key)
	{
		if(!$this->started)
		{
			throw new LogicException(vsprintf("%s(): The session has not been started yet.", [__METHOD__]));
		}

		unset($this->sessionData[$key]);
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
	 * @param   mixed   $default  (optional) Default value
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

/** -------------------- End of file -------------------- **/