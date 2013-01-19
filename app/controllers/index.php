<?php

namespace app\controllers;

use \mako\View;
use \mako\Request;
	
class Index extends \mako\Controller
{

	const RESTFUL = true;

	public function action_index()
	{
		var_dump('action_index');
		Request::factory('index/index', 'GET')->execute();
	}

	public function get_index()
	{
		var_dump('get_index');
	}

	public function post_index()
	{
		var_dump('post_index');
	}

	public function put_index()
	{
		var_dump('put_index');
	}

	public function delete_index()
	{
		var_dump('delete_index');
		Request::factory('index/index', 'GET')->execute();
	}

	// public function delete_index()
	// {

	// }
}