<?php

namespace mako;

use \InvalidArgumentException;

/**
 * Class that generates and validates UUIDs.
 *
 * @author     Frederic G. Østby
 * @author     Andrew Moore (http://www.php.net/manual/en/function.uniqid.php#94959)
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license    
 */

class UUID
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * DNS namespace.
	 *
	 * @var string
	 */

	const DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * URL namespace.
	 *
	 * @var string
	 */

	const URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * ISO OID namespace.
	 *
	 * @var string
	 */

	const OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';

	/**
	 * X.500 DN namespace.
	 *
	 * @var string
	 */

	const X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';

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
	 * Checks if a UUID is valid.
	 *
	 * @access  public
	 * @param   string   $str  The UUID to validate
	 * @return  boolean
	 */

	public static function validate($str)
	{
		return (bool) preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $str);
	}

	/**
	 * Converts UUID to binary.
	 *
	 * @access  protected
	 * @param   string     $namespace  UUID
	 * @return  string
	 */

	protected static function toBin($namespace)
	{
		if(!static::validate($namespace))
		{
			throw new InvalidArgumentException(vsprintf("%s(): Provided namespace is not a valid UUID.", array(__METHOD__)));
		}

		// Get hexadecimal components of namespace

		$nhex = str_replace(array('-','{','}'), '', $namespace);

		// Binary Value

		$nstr = '';

		// Convert Namespace UUID to bits

		for($i = 0; $i < strlen($nhex); $i+=2) 
		{
			$nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
		}

		return $nstr;
	}

	/**
	 * Returns a V3 UUID.
	 *
	 * @access  public
	 * @param   string  $namespace  Namespace
	 * @param   string  $name       Name
	 * @return  string
	 */

	public static function v3($namespace, $name)
	{
		// Calculate hash value
		
		$hash = md5(self::toBin($namespace) . $name);

		return sprintf
		(
			'%08s-%04s-%04x-%04x-%12s',

			// 32 bits for "time_low"

			substr($hash, 0, 8),

			// 16 bits for "time_mid"

			substr($hash, 8, 4),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 3

			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1

			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

			// 48 bits for "node"

			substr($hash, 20, 12)
		);
	}

	/**
	 * Returns a V4 UUID.
	 *
	 * @access  public
	 * @return  string
	 */

	public static function v4()
	{
		return sprintf
		(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

			// 32 bits for "time_low"

			mt_rand(0, 65535),
			mt_rand(0, 65535),

			// 16 bits for "time_mid"

			mt_rand(0, 65535),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4

			mt_rand(16384, 20479),

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1

			mt_rand(32768, 49151),

			// 48 bits for "node"

			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535)
		);
	}

	/**
	 * Returns a V5 UUID.
	 *
	 * @access  public
	 * @param   string  $namespace  Namespace
	 * @param   string  $name       Name
	 * @return  string
	 */

	public static function v5($namespace, $name)
	{
		// Calculate hash value
		
		$hash = sha1(static::toBin($namespace) . $name);

		return sprintf
		(
			'%08s-%04s-%04x-%04x-%12s',

			// 32 bits for "time_low"

			substr($hash, 0, 8),

			// 16 bits for "time_mid"

			substr($hash, 8, 4),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 5

			(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1

			(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,

			// 48 bits for "node"

			substr($hash, 20, 12)
		);
	}
}

/** -------------------- End of file -------------------- **/