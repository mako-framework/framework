<?php

$requestURI   = $_SERVER['REQUEST_URI'];
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

// Strip the query strng
// Need to strip one extra spot for the '?'
if (isset($_SERVER['QUERY_STRING']))
	$requestURI = substr($requestURI, 0, strrpos($requestURI, $_SERVER['QUERY_STRING']) - 1);


if($requestURI !== '/' && file_exists($documentRoot . '/' . $requestURI))
{
	return false; // serve the requested resource as-is.
}

require $documentRoot . '/index.php';
