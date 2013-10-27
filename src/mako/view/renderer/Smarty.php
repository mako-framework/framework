<?php

namespace mako\view\renderer;

/**
 * Smarty template renderer for Mako.
 *
 * @author		Bert Peters
 * @copyright	(c) 2013 Bert Peters
 * @license    http://www.makoframework.com/license
 */

class Smarty extends Template implements RendererInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Smarty instance.
	 *
	 * @var \Smarty
	 */
	private $smarty;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
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
			$config =  array(
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

		$this->view = $view;
		$this->variables = $variables;
		$this->globalVariables = $globalVariables;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Render the template.
	 *
	 * @access  public
	 * @return  string
	 */
	public function render()
	{
		$this->assignVariables($this->variables);
		$this->assignVariables($this->globalVariables);
		return $this->smarty->fetch($this->view);
	}
	
	/**
	 * Assign a given variable set to smarty.
	 *
	 * @access	private
	 * @param	array	$variables	The variables to assign.
	 */
	private function assignVariables($variables)
	{
		foreach ($variables as $key => $value)
		{
			$this->smarty->assign($key, $value);
		}
	}
}
