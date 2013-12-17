<?php

namespace mako\reactor\tasks\console;

use \mako\reactor\io\Input;
use \mako\reactor\io\Output;
use \Exception;

/**
 * Interactive debug console.
 * 
 * Based on PHPA by Dadiv Phillips (http://david.acz.org/).
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Console
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Input
	 * 
	 * @var \mako\reactor\io\Input
	 */

	protected $input;

	/**
	 * Output
	 * 
	 * @var \mako\reactor\io\Output
	 */

	protected $output;

	/**
	 * Is readline available?
	 * 
	 * @var boolean
	 */

	protected $readline;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Input   $input   Input
	 * @param   \mako\reactor\io\Output  $output  Output
	 */

	public function __construct(Input $input, Output $output)
	{
		$this->input = $input;

		$this->output = $output;

		$this->readline = extension_loaded('readline');

		error_reporting(E_ALL | E_STRICT);
		ini_set('error_log', NULL);
		ini_set('log_errors', 1);
		ini_set('display_errors', 0);

		while (ob_get_level())
		{
			ob_end_clean();
		}

		ob_implicit_flush(true);

		if($this->readline)
		{
			if(!$this->input->param('fresh', false))
			{
				@readline_read_history(MAKO_APPLICATION_PATH . '/storage/console_history');
			}

			readline_completion_function([$this, 'autocomplete']);	
		}
	}

	/**
	 * Destructor.
	 * 
	 * @access  public
	 */

	public function __destruct()
	{
		if($this->readline && !$this->input->param('forget', false))
		{
			@readline_write_history(MAKO_APPLICATION_PATH . '/storage/console_history');
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

	protected function autocomplete($line, $pos, $cursor)
	{
		$functions = get_defined_functions();

		$functions = array_merge($functions['internal'], $functions['user']);

		return array_merge(array_keys(get_defined_constants()), array_keys($GLOBALS), $functions);
	}

	/**
	 * Ouput data to the console.
	 * 
	 * @access  protected
	 * @param   string     $ouput  Output
	 */

	protected function output($output)
	{
		$this->output->writeln(' → ' . $output);
	}

	/**
	 * Is immediate?
	 * 
	 * @access  protected
	 * @return  boolean
	 */

	protected function isImmediate($line)
	{
		$skip = 
		[
			'class', 'declare', 'die', 'echo', 'exit', 'for',
			'foreach', 'function', 'global', 'if', 'include',
			'include_once', 'print', 'require', 'require_once',
			'return', 'static', 'switch', 'unset', 'while'
		];

		$okeq = ['===', '!==', '==', '!=', '<=', '>='];

		$sq = false;
		$dq = false;

		$code = '';

		for($i = 0; $i < strlen($line); $i++)
		{
			$c = $line{$i};

			if($c == '\'')
			{
				$sq = !$sq;
			}
			elseif($c == '"')
			{
				$dq = !$dq;
			}
			elseif(($sq) || ($dq))
			{
				if($c == '\\')
				{
					$i++;
				}
			}
			else
			{
				$code .= $c;
			}
		}

		$code = str_replace($okeq, '', $code);

		if(strcspn($code, ';{=') != strlen($code))
		{
			return false;
		}

		$kw = preg_split('/[^A-Za-z0-9_]/', $code);

		foreach($kw as $i)
		{
			if(in_array($i, $skip))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Starts the interactive console.
	 * 
	 * @access  public
	 */

	public function run()
	{
		while(true)
		{
			if($this->readline)
			{
				$__input = readline('mako> ');
			}
			else
			{
				fwrite(STDOUT, 'mako> ');

				$__input = fgets(STDIN);
			}

			$__input = rtrim(trim($__input), ';');

			if(empty($__input))
			{
				continue;
			}

			if(in_array($__input, ['exit', 'die', 'quit']))
			{
				break;
			}

			if($this->readline)
			{
				readline_add_history($__input);
			}

			if($this->isImmediate($__input))
			{
				$__input = 'return (' . $__input . ')';
			}

			ob_start();

			try
			{
				$__return = eval('unset($__input); ' . $__input . ';');
			}
			catch(Exception $e)
			{
				$this->output->error($e->getMessage());

				continue;
			}

			if(ob_get_length() == 0)
			{
				if(is_bool($__return))
				{
					$this->output(($__return ? 'true' : 'false'));
				}
				elseif(is_string($__return))
				{
					$this->output('\'' . addcslashes($__return, "\0..\37\177..\377") . '\'');
				}
				elseif(!is_null($__return))
				{
					$this->output(var_export($__return, true));
				}
			}

			unset($__return);

			$__output = ob_get_contents();

			ob_end_clean();

			if((strlen($__output) > 0))
			{
				$this->output($__output);
			}

			unset($__output);
		}
	}
}

/** -------------------- End of file -------------------- **/