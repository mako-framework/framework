<?php

use \mako\CLI;
use \mako\String;

class Example extends \mako\reactor\Task
{
	public function run($name = 'world')
	{
		CLI::stdout('Hello ' . $name . '!');
	}

	public function random()
	{
		$colors = array('red', 'purple', 'cyan', 'green', 'yellow', 'blue');
		
		$key = array_rand($colors);

		CLI::stdout('Random ' . $colors[$key] . ' string: ' . CLI::color(String::random(), $colors[$key]));
	}
}