<?php

namespace mako;

use \SimpleXMLElement;

/**
 * Converts arrays to different data formats.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Format
{
	//---------------------------------------------
	// Class properties
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
	 * Converts an array to JSON.
	 *
	 * @access  public
	 * @param   array   $data  Array to convert
	 * @return  string
	 */

	public static function json(array $data)
	{
		$data = json_encode($data);

		if(!empty($_GET['jsoncallback']))
		{
			$data = $_GET['jsoncallback'] . '(' . $data . ')';
		}

		return $data;
	}

	/**
	 * Converts an array to XML.
	 *
	 * @access  public
	 * @param   array   $data         Array to convert
	 * @param   string  $rootNode     (optional) Root node name
	 * @param   string  $unknownNode  (optional) Unknown node name
	 * @param   string  $charset      (optional) Character set
	 * @return  string
	 */

	public static function xml(array $data, $rootNode = 'items', $unknownNode = 'item', $charset = MAKO_CHARSET)
	{
		$xml = new SimpleXMLElement('<?xml version="1.0" encoding="' . $charset . '"?><' . $rootNode . '/>');

		$func = function($data, $xml) use (&$func, $unknownNode, $charset)
		{
			foreach($data as $key => $value)
			{
				$key = preg_replace('/[^_\-0-9-a-z]/i', '', is_int($key) ? $unknownNode : $key);

				if(is_array($value) || is_object($value))
				{
					$func((array) $value, $xml->addChild($key));
				}
				else
				{
					$xml->addChild($key, htmlspecialchars($value, ENT_NOQUOTES, $charset, false));
				}
			}

			return $xml;
		};

		$func($data, $xml);

		return trim($xml->asXML());
	}

	/**
	 * Converts an array to CSV.
	 *
	 * @access  public
	 * @param   array   $data  Array to convert
	 * @return  string
	 */

	public static function csv(array $data)
	{
		if(!isset($data[0]) || (!is_array($data[0]) && !is_object($data[0])))
		{
			$data = array($data);
		}

		if(is_string(key((array) $data[0])))
		{
			$fields = array_keys((array) $data[0]);

			array_unshift($data, $fields);	
		}

		$handle = fopen('php://temp', 'rw');

		foreach($data as $fields)
		{
			fputcsv($handle, (array) $fields);
		}

		rewind($handle);

		$csv = stream_get_contents($handle);

		fclose($handle);

		return rtrim($csv, "\n");
	}
}

/** -------------------- End of file --------------------**/