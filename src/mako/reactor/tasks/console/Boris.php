<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\reactor\tasks\console;

use \Boris\Boris as REPL;

use \mako\reactor\io\Input;
use \mako\reactor\io\Output;

/**
 * Boris wrapper.
 *
 * @author  Frederic G. Østby
 */

class Boris
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Input.
	 * 
	 * @var \mako\reactor\io\Input
	 */

	protected $input;

	/**
	 * Output.
	 * 
	 * @var \mako\reactor\io\Output
	 */

	protected $output;

	/**
	 * Path to history file.
	 * 
	 * @var string
	 */

	protected $history;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input   $input    Input
	 * @param   \mako\reactor\io\Output  $output   Output
	 * @param   string                   $history  Path to history file
	 */

	public function __construct(Input $input, Output $output, $history)
	{
		$this->input = $input;

		$this->output = $output;

		$this->history = $history;

		// Set error reporting

		error_reporting(E_ALL | E_STRICT);

		// Enable autocompletion

		readline_completion_function(array($this, 'autocomplete'));

		// Delete the history file if the user wants to start a fresh session

		if($this->input->param('fresh', false))
		{
			@unlink($this->history);
		}
	}

	/**
	 * Destructor.
	 * 
	 * @access  public
	 */

	public function __destruct()
	{
		// Delete the history file if the user wants to forget

		if($this->input->param('forget', false))
		{
			@unlink($this->history);
		}
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns an array of all the autocomplete items.
	 * 
	 * @access protected
	 */

	public function autocomplete($line, $pos, $cursor)
	{
		$functions = get_defined_functions();

		$functions = array_merge($functions['internal'], $functions['user']);

		return array_merge(array_keys(get_defined_constants()), array_keys($GLOBALS), $functions);
	}

	/**
	 * Starts the interactive console.
	 * 
	 * @access  public
	 */

	public function run()
	{
		// Print welcome message

		$this->output->writeln('Welcome to the <green>Mako</green> debug console. Type <yellow>exit;</yellow> or <yellow>die;</yellow> to exit.');

		$this->output->nl();

		// Disable mako error handlers

		restore_error_handler();
		restore_exception_handler();
		define('MAKO_DISABLE_FATAL_ERROR_HANDLER', true);

		// Start Boris REPL

		$boris = new REPL('mako> ', $this->history);
		
		$boris->start();
	}
}

