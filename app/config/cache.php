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
	* The supported cache types are: "apc", "file", "memcache", "memcached", "memory", "redis", "sqlite", "wincache", "xcache", "zenddisk" and "zendmemory".
	*
	* type         : Cache type you want to use.
	* identifier   : Cache identifier that should be unique to your application to avoid conflicts.
	* path         : Cache path (only required when using "file" cache).
	* compress_data: Compress stored items? (this requires zlib and is only available when using "memcache" or "memcached" cache).
	* timeout      : Value in seconds which will be used for connecting to the daemon (only required when using "memcache" or "memcached" cache).
	* servers      : Cache servers (you can use multiple servers and it is only required when using "memcache" or "memcached" cache).
	* configuration: Redis configuration to use for caching (only required when using "redis" cache).
	* username     : Cache username (only required when using "xcache" cache).
	* password     : Cache password (only required when using "xcache" cache).
	*/
	
	'configurations' => array
	(
		'apc' => array
		(
			'type'       => 'apc',
			'identifier' => MAKO_APPLICATION,
		),

		'file' => array
		(
			'type'       => 'file',
			'identifier' => MAKO_APPLICATION,
			'path'       => MAKO_APPLICATION . '/storage/cache',
		),

		'memcache' => array
		(
			'type'          => 'memcache',
			'identifier'    => MAKO_APPLICATION,
			'compress_data' => false,
			'timeout'       => 1,
			'servers'       => array
			(
				'server_1' => array
				(
					'server' => 'localhost',
					'port' => '11211',
					'persistent_connection' => false,
					'weight' => 1,
				),
			),
		),
		
		'memcached' => array
		(
			'type'          => 'memcached',
			'identifier'    => MAKO_APPLICATION,
			'compress_data' => false,
			'timeout'       => 1,
			'servers'       => array
			(
				'server_1' => array
				(
					'server' => 'localhost',
					'port' => '11211',
					'weight' => 1,
				),
			),
		),
		
		'memory' => array
		(
			'type' => 'memory',
		),

		'redis' => array
		(
			'type'          => 'redis',
			'identifier'    => MAKO_APPLICATION,
			'configuration' => 'cache',
		),

		'sqlite' => array
		(
			'type'       => 'sqlite',
			'identifier' => MAKO_APPLICATION,
		),

		'wincache' => array
		(
			'type'       => 'wincache',
			'identifier' => MAKO_APPLICATION,
		),

		'xcache' => array
		(
			'type'       => 'xcache',
			'identifier' => MAKO_APPLICATION,
			'username'   => 'xcache',
			'password'   => 'xcache',
		),

		'zenddisk' => array
		(
			'type'       => 'zenddisk',
			'identifier' => MAKO_APPLICATION,
		),

		'zendmemory' => array
		(
			'type'       => 'zendmemory',
			'identifier' => MAKO_APPLICATION,
		),
	),
);

/** -------------------- End of file --------------------**/