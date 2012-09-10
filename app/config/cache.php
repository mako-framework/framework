<?php

//---------------------------------------------
// Cache configuration
//---------------------------------------------

return array
(
	/**
	 * Default configuration to use.
	 */
	
	'default' => 'file',
	
	/**
	 * You can define as many cache configurations as you want.
	 *
	 * The supported cache types are: "APC", "Database", "File", "Memcache", "Memcached", "Memory", "Redis", "WinCache", "XCache", "ZendDisk" and "ZendMemory".
	 *
	 * type         : Cache type you want to use (case-sensitive).
	 * identifier   : Cache identifier that should be unique to your application to avoid conflicts.
	 * path         : Cache path (only required when using "File" cache).
	 * compress_data: Compress stored items? (this requires zlib and is only available when using "Memcache" or "Memcached" cache).
	 * timeout      : Value in seconds which will be used for connecting to the daemon (only required when using "Memcache" or "Memcached" cache).
	 * servers      : Cache servers (you can use multiple servers and it is only required when using "Memcache" or "Memcached" cache).
	 * configuration: Configuration to use for caching (only required when using "Database" or "Redis" cache).
	 * table        : Name of the database table (only required when using "Database" cache).
	 * username     : Cache username (only required when using "XCache" cache).
	 * password     : Cache password (only required when using "XCache" cache).
	 */
	
	'configurations' => array
	(
		'apc' => array
		(
			'type'       => 'APC',
			'identifier' => MAKO_APPLICATION_ID,
		),

		'database' => array
		(
			'type'          => 'Database',
			'identifier'    => MAKO_APPLICATION_ID,
			'configuration' => 'test',
			'table'         => 'mako_cache',
		),

		'file' => array
		(
			'type'       => 'File',
			'identifier' => MAKO_APPLICATION_ID,
			'path'       => MAKO_APPLICATION_PATH . '/storage/cache',
		),

		'memcache' => array
		(
			'type'          => 'Memcache',
			'identifier'    => MAKO_APPLICATION_ID,
			'compress_data' => false,
			'timeout'       => 1,
			'servers'       => array
			(
				'server_1' => array
				(
					'server'                => 'localhost',
					'port'                  => '11211',
					'persistent_connection' => false,
					'weight'                => 1,
				),
			),
		),
		
		'memcached' => array
		(
			'type'          => 'Memcached',
			'identifier'    => MAKO_APPLICATION_ID,
			'compress_data' => false,
			'timeout'       => 1,
			'servers'       => array
			(
				'server_1' => array
				(
					'server' => 'localhost',
					'port'   => '11211',
					'weight' => 1,
				),
			),
		),
		
		'memory' => array
		(
			'type' => 'Memory',
		),

		'redis' => array
		(
			'type'          => 'Redis',
			'identifier'    => MAKO_APPLICATION_ID,
			'configuration' => 'cache',
		),

		'wincache' => array
		(
			'type'       => 'WinCache',
			'identifier' => MAKO_APPLICATION_ID,
		),

		'xcache' => array
		(
			'type'       => 'XCache',
			'identifier' => MAKO_APPLICATION_ID,
			'username'   => 'xcache',
			'password'   => 'xcache',
		),

		'zenddisk' => array
		(
			'type'       => 'ZendDisk',
			'identifier' => MAKO_APPLICATION_ID,
		),

		'zendmemory' => array
		(
			'type'       => 'ZendMemory',
			'identifier' => MAKO_APPLICATION_ID,
		),
	),
);

/** -------------------- End of file --------------------**/