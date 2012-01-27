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
		CLI::stdout('Random purple string: ' . CLI::color(String::random(), 'purple'));
	}
}