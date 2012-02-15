<?php

namespace mako;

use \SimpleXMLElement;

/**
* Convert arrays to different formats.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class ArrayTo
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
	* Converts an array to JSON.
	*
	* @access  public
	* @param   array   Array to convert
	* @return  string
	*/

	public static function json(array $data)
	{
		$data = json_encode($data);

		if(isset($_GET['jsoncallback']))
		{
			$data = $_GET['jsoncallback'] . '(' . $data . ')';
		}

		return $data;
	}

	/**
	* Converts an array to XML.
	*
	* @access  public
	* @param   array   Array to convert
	* @param   string  (optional) Root node name
	* @param   string  (optional) Unknown node name
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

				if(is_array($value))
				{
					$func($value, $xml->addChild($key));
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
	* @param   array   Array to convert
	* @return  string
	*/

	public static function csv(array $data)
	{
		if(!isset($data[0]) || !is_array($data[0]))
		{
			$data = array($data);	
		}

		if(is_string(key($data[0])))
		{
			$fields = array_keys($data[0]);

			array_unshift($data, $fields);	
		}

		$handle = fopen('php://temp', 'rw');

		foreach($data as $fields)
		{
			fputcsv($handle, $fields);
		}

		rewind($handle);

		$csv = stream_get_contents($handle);

		fclose($handle);

		return rtrim($csv, "\n");
	}
}

/** -------------------- End of file --------------------**/