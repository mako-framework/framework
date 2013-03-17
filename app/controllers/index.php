<?php

namespace app\controllers;

use \mako\View;

class Index extends \mako\Controller
{
	public function action_index()
	{
		echo $lol;
		
		return new View('welcome');
	}
}