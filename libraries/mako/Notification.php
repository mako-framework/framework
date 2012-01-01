<?php

namespace mako
{
	use \mako\Session;

	/**
	* Class that handles notifications (aka "flash messages").
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Notification
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		// Nothing here

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*
		* @access  protected
		*/

		protected function __construct()
		{
			// Nothing here
		}
		
		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Starts session if it doesn't exist.
		*
		* @access  protected
		*/
		
		protected static function sessionStart()
		{
			if(session_id() === '')
			{
				Session::start();
			}
		}

		/**
		* Set the notification.
		*
		* @access  public
		* @param   string  Notification message
		* @return  void
		*/

		public static function set($message)
		{
			static::sessionStart();
			
			$_SESSION[MAKO_APPLICATION_ID . '_notification'] = $message;
		}

		/**
		* Gets the notification.
		*
		* @access  public
		* @return  string  Returns the notification or false if there is no message
		*/

		public static function get()
		{
			static::sessionStart();
			
			if(isset($_SESSION[MAKO_APPLICATION_ID . '_notification']))
			{
				$message = $_SESSION[MAKO_APPLICATION_ID . '_notification'];

				unset($_SESSION[MAKO_APPLICATION_ID . '_notification']);

				return $message;
			}
			else
			{
				return false;
			}
		}
	}
}
/** -------------------- End of file --------------------**/