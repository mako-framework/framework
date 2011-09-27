<?php

namespace mako
{
	/**
	* Class that makes it easy to implement Gravatar in your application.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Gravatar
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* URL to the API server.
		*/

		const API_SERVER = 'http://www.gravatar.com/avatar/';

		/**
		* URL to the secure API server.
		*/

		const API_SECURE_SERVER = 'https://secure.gravatar.com/avatar/';

		/**
		* Default avatar size in pixels.
		*/

		protected $avatarSize = 80;

		/**
		* Default avatar rating.
		*/

		protected $avatarRating = 'g';

		/**
		* URL to the default avatar.
		*/

		protected $defaultAvatar = 'mm';

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//------------------------------------------------

		/**
		* Constructor.
		*
		* @access  public
		*/

		public function __construct()
		{
			// Nothing here
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @return  Gravatar
		*/

		public static function factory()
		{
			return new static();
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Set the size of the Gravatar. Value must be between 1 and 512.
		*
		* @access  public
		* @param   int       Size of the avatar in pixels
		* @return  Gravatar
		*/

		public function setSize($size)
		{
			$this->avatarSize = ((int) $size <= 512 && (int) $size >= 1) ? (int) $size : 80;

			return $this;
		}

		/**
		* Set the max rating of the Gravatar.
		*
		* @access  public
		* @param   string    Maximum rating of the gravatar
		* @return  Gravatar
		*/

		public function setRating($rating)
		{
			$ratings = array('g', 'pg', 'r', 'x');

			$this->avatarRating = in_array($rating, $ratings) ? $rating : 'g';

			return $this;
		}

		/**
		* Set the url to the default Gravatar.
		*
		* @access  public
		* @param   string    URL to a default avatar image
		* @return  Gravatar
		*/

		public function setDefault($default)
		{
			$this->defaultAvatar = rawurlencode($default);

			return $this;
		}
		
		/**
		* Get the image size of the Gravatar.
		*
		* @access  public
		* @return  int
		*/

		public function getSize()
		{
			return $this->avatarSize;
		}

		/**
		* Get the url of the Gravatar.
		*
		* @access  public
		* @param   string  Email address
		* @return  string
		*/

		public function getAvatar($email, $ssl = false)
		{
			$server = ($ssl === true) ? static::API_SECURE_SERVER : static::API_SERVER;

			return $server . md5(trim(mb_strtolower($email))) . ".jpg?r={$this->avatarRating}&amp;s={$this->avatarSize}&amp;d={$this->defaultAvatar}";
		}
	}
}

/** -------------------- End of file --------------------**/