<?php

use \mako\Arr;
use \mako\String;

class Example extends \mako\reactor\Task
{
	/**
	 * Demo of task action with optional parameters.
	 * 
	 * @access  public
	 */
	
	public function run($name = 'world')
	{
		$this->cli->stdout('Hello ' . $name . '!');
	}

	/**
	 * Demo of CLI color output
	 * 
	 * @access  public
	 */

	public function random()
	{
		$color = Arr::random(array('red', 'purple', 'cyan', 'green', 'yellow', 'blue'));

		$this->cli->stdout('Random ' . $color . ' string: ' . $this->cli->color(String::random(), $color));
	}

	/**
	 * Demo of CLI input
	 * 
	 * @access  public
	 */

	public function input()
	{
		$name = $this->cli->input('Enter your name');
		$like = $this->cli->confirm('Do you like beer?');

		$this->cli->wait(5);

		$this->cli->stdout($name . ($like ? ' likes beer' : ' doesn\'t like beer'));
	}
}