<?php

namespace app\controllers
{
	use \mako\View;
	
	class Index extends \mako\Controller
	{
		public function _index()
		{
			echo new View('welcome', array('welcome' => 'Welcome to the Mako Framework!'));
		}
	}
}