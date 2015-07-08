<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

// Force script name so that clean URLs work

$_SERVER['SCRIPT_NAME'] = '/index.php';

$requestURI   = $_SERVER['REQUEST_URI'];
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

// Strip the query string so that files can be served even if they have a query string
// This can be useful for versioning assets such as images, CSS and JavaScript

if(isset($_SERVER['QUERY_STRING']))
{
	$requestURI = substr($requestURI, 0, strrpos($requestURI, $_SERVER['QUERY_STRING']) - 1);
}

// Check if the file exists

if($requestURI !== '/' && file_exists($documentRoot . '/' . $requestURI))
{
	return false; // serve the requested resource as-is.
}

// File doesn't exist. Route request to index.php

require $documentRoot . '/index.php';