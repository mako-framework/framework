<?php

$requestURI   = $_SERVER['REQUEST_URI'];
$documentRoot = $_SERVER['DOCUMENT_ROOT'];

if($requestURI !== '/' && file_exists($documentRoot . '/' . $requestURI))
{
	return false; // serve the requested resource as-is.
}

require $documentRoot . '/index.php';