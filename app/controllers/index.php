<?php

namespace app\controllers;

use \mako\View;
use \mako\Input;

class Index extends \mako\Controller
{
	const RESTFUL = true;

	public function get_index()
	{

	}

	public function post_index()
	{

	}

	public function put_index()
	{
		Input::has('login', 'password')
		Input::has(array('login', 'password'), 'GET')
		if(Input::has('login', 'password'))
		{
			var_dump(Input::data('login'));
			var_dump(Input::data('password'));
		}
	}

	public function delete_index()
	{

	}
}