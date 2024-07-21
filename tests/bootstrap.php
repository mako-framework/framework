<?php

error_reporting(E_ALL);

date_default_timezone_set('UTC');

setlocale(LC_ALL, 'C');

mb_language('uni');
mb_regex_encoding('UTF-8');
mb_internal_encoding('UTF-8');

set_error_handler(function ($code, $message, $file, $line) {
	if ((error_reporting() & $code) !== 0) {
		throw new ErrorException($message, $code, 0, $file, $line);
	}

	return true;
});

require_once dirname(__DIR__) . '/vendor/autoload.php';
