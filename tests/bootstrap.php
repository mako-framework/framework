<?php

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('UTC');

setlocale(LC_ALL, 'C');

mb_language('uni');
mb_regex_encoding('UTF-8');
mb_internal_encoding('UTF-8');

require_once dirname(__DIR__) . '/vendor/autoload.php';
