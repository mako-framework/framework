<?php

namespace mako\view\renderers;

use \Smarty;

/**
 * Mako Smarty renderer.
 *
 * @author		Bert Peters
 * @copyright	(c) 2013 Bert Peters
 * @license    http://www.makoframework.com/license
 */

class SmartyRenderer extends \mako\view\renderers\Template
 implements \mako\view\renderers\RendererInterface{

	/**
	 * Smarty instance
	 *
	 * @var Smarty
	 */
	private $smarty;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 *
	 * Store the view parameters and initialize the Smarty object.
	 * 
	 * @access  public
	 * @param   string  $view             View path
	 * @param   array   $variables        View variables
	 * @param   array   $globalVariables  Global view variables
	 */

	public function __construct($view, array $variables, array $globalVariables)
	{

		try {
			$config = \mako\Config::get("smarty");
		} catch (\RuntimeException $e) {
			// Smarty config doesn't exist, just roll with it.
			$config = array(
				"templateDir" => MAKO_APPLICATION_PATH . "/views",
				"cacheDir" => MAKO_APPLICATION_PATH . "/storage/smarty/cache",
				"compileDir" => MAKO_APPLICATION_PATH . "/storage/smarty/compile"
			);

		}

		// Initialize a smarty instance
		$this->smarty = new \Smarty();
		$this->smarty->setTemplateDir($config['templateDir']);
		$this->smarty->setCompileDir($config['compileDir']);
		$this->smarty->setCacheDir($config['cacheDir']);

		// Save the view parameters
		$this->variables = $variables;
		$this->globalVariables = $globalVariables;
		$this->view = $view;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Return the rendered view.
	 *
	 * @access public
	 */
	public function render()
	{
		// Assign the view-variables.
		$this->assignVariables($this->variables);
		$this->assignVariables($this->globalVariables);

		// Let smarty render
		return $this->smarty->fetch($this->view);
	}

	/**
	 * Assign all variables in given array to Smarty.
	 *
	 * @access	private
	 * @param	array	$variables	Variables to be added.
	 */
	private function assignVariables($variables)
	{
		foreach ($variables as $key => $value)
		{
			$this->smarty->assign($key, $value);
		}
	}
}


