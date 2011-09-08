<?php

namespace app\controllers
{
	use \mako\View;
	
	class Index extends \mako\Controller
	{
		public function _index()
		{
			View::factory('welcome')
			->assign('welcome', 'Welcome to the Mako Framework!')
			->display();
		}
	}
}