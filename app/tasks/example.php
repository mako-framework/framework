<?php

use \mako\CLI;
use \mako\Arr;
use \mako\String;

class Example extends \mako\reactor\Task
{
	public function run($name = 'world')
	{
		CLI::stdout('Hello ' . $name . '!');
	}

	public function random()
	{
		$color = Arr::random(array('red', 'purple', 'cyan', 'green', 'yellow', 'blue'));

		CLI::stdout('Random ' . $color . ' string: ' . CLI::color(String::random(), $color));
	}
}