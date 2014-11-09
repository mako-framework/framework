<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator\plugins;

use \mako\database\ConnectionManager;
use \mako\validator\plugins\ValidatorPlugin;

/**
 * Database exists plugin.
 *
 * @author  Frederic G. Østby
 */

class DatabaseExistsValidator extends ValidatorPlugin
{
	/**
	 * Rule name.
	 * 
	 * @var string
	 */

	protected $ruleName = 'exists';

	/**
	 * Connection manager instance.
	 * 
	 * @var \mako\database\ConnectionManager
	 */

	protected $connectionManager;

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\ConnectionManager  $connectionManager  Connection manager instance
	 */

	public function __construct(ConnectionManager $connectionManager)
	{
		$this->connectionManager = $connectionManager;
	}

	/**
	 * {@inheritdoc}
	 */

	public function validate($input, $parameters)
	{
        // Start query

        $query = $this->connectionManager->builder()->table($parameters[0])->where($parameters[1], '=', $input);

        // Unset 'table' and 'column' parameters

        unset($parameters[0]);
        unset($parameters[1]);

        // Reset array keys from parameters

        $parameters = array_values($parameters);

        // Check extra parameters

        if($parameters && (count($parameters) % 3 == 0))
        {
            for($i = 0; $i < count($parameters); $i += 3)
            {
                $query->where($parameters[$i], $parameters[$i+1], $parameters[$i+2]);
            }
        }

        return ($query->count() != 0);
	}
}
