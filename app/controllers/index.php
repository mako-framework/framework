<?php

namespace app\controllers;

use \mako\View;

class Index extends \mako\Controller
{
	public function action_index()
	{
		return new View('welcome');
	}
}