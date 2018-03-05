### 5.4.0 <small> (2018-03-05)</small>

Update using ```composer update```.

#### New

* Added `Application::startTime()` method.
* The container now supports replacing previously registered/resolved items:
	- Added `Container::replace()` method.
	- Added `Container::replaceSingleton()` method.
	- Added `Container::replaceInstance()` method.
	- Added `Container::onReplace()` method.
* Added `Request::contentType()` method.
* Added `Response::reset()` method.
* Added `mako\cli\output\Output::hasAnsiSupport()` method.
* Added `mako\cli\output\Output::clearLine()` method.
* Added `mako\cli\output\Output::clearLines()` method.
* Added `mako\cli\output\formatter\Formatter::stripSGR()` method.
* Added `mako\cli\output\formatter\FormatterInterface::stripTags()` method.
* Added `mako\cli\output\helpers\ProgressBar::remove()` method.
* Added `mako\cli\output\helpers\ProgressBar::setPrefix()` method.
	- Added optional `$prefix` parameter to the `Command::progressBar()` method.
* Added optional `$priority` parameter to the `mako\http\routing\Dispatcher::registerMiddleware()` method.

#### Changes

* The `Container::factory()` method is now public.
* The `Request::getBody()` method now returns an instance of `mako\http\request\Body`.
* Removed `Request` methods that where deprecated in 5.3:
	- Removed the `Request::get()` method.
	- Removed the `Request::post()` method.
	- Removed the `Request::put()` method.
	- Removed the `Request::patch()` method.
	- Removed the `Request::delete()` method.
	- Removed the `Request::cookie()` method.
	- Removed the `Request::signedCookie()` method.
	- Removed the `Request::file()` method.
	- Removed the `Request::server()` method.
	- Removed the `Request::has()` method.
	- Removed the `Request::data()` method.
	- Removed the `Request::whitelisted()` method.
	- Removed the `Request::blacklisted()` method.
	- Removed the `Request::header()` method.
	- Removed the `Request::acceptableContentTypes()` method.
	- Removed the `Request::acceptableLanguages()` method.
	- Removed the `Request::acceptableCharsets()` method.
	- Removed the `Request::acceptableEncodings()` method.
* Removed `Response` filters:
	- Removed the `Response::filter()` method.
	- Removed the `Response::getFilters()` method.
	- Removed the `Response::clearFilters()` method.
* The `Response::getHeaders()` method now returns a response header collection.
* The `Response::getCookies()` method now returns a response cookie collection.
* Removed the `mako\cli\output\formatter\Formatter::hasAnsiSupport()` method.
* Removed the `mako\cli\output\formatter\FormatterInterface::strip()` method.
* Arguments are now converted to camel case before being passed to the `execute` method of reactor commands.

#### Deprecations

* Deprecated the `Response::header()` method.
* Deprecated the `Response::hasHeader()` method.
* Deprecated the `Response::removeHeader()` method.
* Deprecated the `Response::clearHeaders()` method.
* Deprecated the `Response::cookie()` method.
* Deprecated the `Response::signedCookie()` method.
* Deprecated the `Response::deleteCookie()` method.
* Deprecated the `Response::hasCookie()` method.
* Deprecated the `Response::removeCookie()` method.
* Deprecated the `Response::clearCookies()` method.

#### Improvements

* Unit tests now run using PHPUnit 6.
* Removed unnecessary function calls in Redis client.
* Various improvements and optimizations.

> Check out the upgrade guide for details on how to upgrade from `5.3.*.`

--------------------------------------------------------

### 5.3.2 <small> (2018-03-01)</small>

Update using ```composer update```.

#### Improvements

* The `Request::getData()` method will now return the parsed body for POST requests that do not contain form data.

--------------------------------------------------------

### 5.3.1 <small> (2018-01-24)</small>

Update using ```composer update```.

#### Bugfixes

* The `GD` image processor will no longer fail when using uppercase file extensions when saving.

#### Changes

* The `GD` image processor now uses `imagecopyresampled` instead of `imagecopyresized` when resizing images.

#### Improvements

* Columns with `null` values will no longer be updated unnecessarily when using `ORM::save()`.

--------------------------------------------------------

### 5.3.0 <small> (2018-01-10)</small>

Update using ```composer update```.

#### New

* Added ```Connection::pairs()``` method.
* Added ```Query::pairs()``` method.
* Added ```Collection::merge()``` method.
* ```Collection::map()``` callables can now accept the item key as a second argument.
* ```Collection::filter()``` callables can now accept the item key as a second argument.
* It is now possible to auto assign variables to views using the ```ViewFactory::autoAssign()``` method.
* JSON responses can now set the response status code.
* It is now possible to capture output in a template using the new `{% capture:name %}...{% endcapture %}` blocks.
* Added `{% nospace %}...{% endnospace %}` blocks to template syntax that will remove all whitespace between HTML tags.
* Added `PaginationInterface::isValidPage()` method.
* Mako is now using Whoops for displaying exception details.
* It is now possible to register route constraints with the router.
* It is now possible to set and update junction attributes.
* Added `Request::getQuery()` method.
* Added `Request::getPost()` method.
* Added `Request::getHeaders()` method.
* Added `Request::getCookies()` method.
* Added `Request::getBody()` method.
* Added `Request::getData()` method.
* Added `Request::getFiles()` method.
* Added `Request::getServer()` method.
* Added `Parameters::whitelisted()` method.
* Added `Parameters::blacklisted()` method.

#### Changes

* Moved the `ContainerAwareTrait` trait to the `mako\syringe\traits` namespace.
* Moved the `ControllerHelperTrait` trait to the `mako\http\routing\traits` namespace.
* Moved the `ConfigurableTrait` trait to the `mako\common\traits` namespace.
* Moved the `ExtendableTrait` trait to the `mako\common\traits` namespace.
* Moved the `FunctionParserTrait` trait to the `mako\common\traits` namespace.
* Moved the `NamespacedFileLoaderTrait` trait to the `mako\common\traits` namespace.
* The `Str::slug()` method now uses `rawurlencode` instead of `urlencode`.
* All HTTP middleware must now implement the `mako\http\routing\middleware\MiddlewareInterface`.
* HTTP middleware is now registered with the route dispatcher.
* The `Route::when()` method has been renamed to `patterns`.
* Renamed the `Request::body()` method to `getRawBody`.
* Renamed the `Request::bodyAsStream` method `getRawBodyAsStream`.

#### Deprecations

* Deprecated the `Request::get()` method.
* Deprecated the `Request::post()` method.
* Deprecated the `Request::put()` method.
* Deprecated the `Request::patch()` method.
* Deprecated the `Request::delete()` method.
* Deprecated the `Request::cookie()` method.
* Deprecated the `Request::signedCookie()` method.
* Deprecated the `Request::file()` method.
* Deprecated the `Request::server()` method.
* Deprecated the `Request::has()` method.
* Deprecated the `Request::data()` method.
* Deprecated the `Request::whitelisted()` method.
* Deprecated the `Request::blacklisted()` method.
* Deprecated the `Request::header()` method.
* Deprecated the `Request::acceptableContentTypes()` method.
* Deprecated the `Request::acceptableLanguages()` method.
* Deprecated the `Request::acceptableCharsets()` method.
* Deprecated the `Request::acceptableEncodings()` method.

> Deprecated methods will be removed in 5.4.0.

> Check out the upgrade guide for details on how to upgrade from `5.2.*.`

--------------------------------------------------------

### 5.2.11 <small> (2017-11-30)</small>

Update using ```composer update```.

#### Bugfixes

* Fixes issue that prevents accidental login after logout. The issue would occur if the `mako\gatekeeper\adapters\Session::getUser()` method got called after the `mako\gatekeeper\adapters\Session::logout()` method if the user had a "remember me" cookie.

--------------------------------------------------------

### 5.2.10 <small> (2017-11-18)</small>

Update using ```composer update```.

#### Bugfixes

* The ```Memcache::putIfNotexists()``` and ```Memcached::putIfNotexists()``` methods will now support TTLs longer than 30 days.

--------------------------------------------------------

### 5.2.9 <small> (2017-10-26)</small>

Update using ```composer update```.

#### Bugfixes

* Errors will now be logged even if the default error handler isn't called.

--------------------------------------------------------

### 5.2.8 <small> (2017-10-13)</small>

Update using ```composer update```.

#### Bugfixes

* ```ManyToMany::synchronize()``` will no longer execute unnecessary and invalid queries that cause exceptions.

--------------------------------------------------------

### 5.2.7 <small> (2017-10-12)</small>

Update using ```composer update```.

#### Bugfixes

* Package config overrides will now merge properly with original config.

--------------------------------------------------------

### 5.2.6 <small> (2017-10-11)</small>

Update using ```composer update```.

#### Bugfixes

* Package config overrides now merges with original config.

--------------------------------------------------------

### 5.2.5 <small> (2017-10-07)</small>

Update using ```composer update```.

#### New

* Now possible to insert databases rows with only default values.

--------------------------------------------------------

### 5.2.4 <small> (2017-09-21)</small>

Update using ```composer update```.

#### New

* Added ```ManyToMany::alongWith()``` method.

--------------------------------------------------------

### 5.2.3 <small> (2017-08-22)</small>

Update using ```composer update```.

#### Bugfixes

* Batch queries will no longer fail when having criteria.

--------------------------------------------------------

### 5.2.2 <small> (2017-08-16)</small>

Update using ```composer update```.

#### Bugfixes

* Will now automatically unlock locked used accounts when the lock time has expired.

--------------------------------------------------------

### 5.2.1 <small> (2017-06-06)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed language cache bug that was introduced in 5.2.0.

--------------------------------------------------------

### 5.2.0 <small> (2017-05-08)</small>

Update using ```composer update```.

#### New

* Added ```putIfNotexists()``` method to all cache adapters.
* The ```apcu```, ```memcached```, ```memory```, ```null``` and ```redis``` cache stores now implement the new ```mako\cache\stores\IncrementDecrementInterface``` interface.
* Added ```Collection::getValues()``` method.
* Added ```Collection::each()``` method.
* Added ```Collection::map()``` method.
* Added ```Collection::filter()``` method.
* It is now possible to pass custom PDO options to a connection.
* It is now possible to format numbers in i18n strings using the ```<number>``` tag.

#### Changes

* The ```Gatekeeper``` library has been rewritten. It is now possible to implement custom authentication adapters.
* The ```Gatekeeper::basicAuth()``` method will now always return a boolean value.
* Headers will now be set with the case that they where defined with.
* The ```CacheManager::instance()``` method now returns a ```mako\cache\stores\StoreInterface``` instance instead of a ```mako\cache\Cache``` instance.

#### Bugfixes

* The reactor ```--env``` flag now works as expected.

#### Improvements

* The ORM will now use fully qualified column names in the relation query criterion.
* Various improvements and optimizations.

> Check out the upgrade guide for details on how to upgrade from ```5.1.*.```

--------------------------------------------------------

### 5.1.3 <small> (2017-02-17)</small>

Update using ```composer update```.

#### Bugfixes

* Request::getParsed() no longer fails if the content type header contains a character set.

--------------------------------------------------------

### 5.1.2 <small> (2017-01-25)</small>

Update using ```composer update```.

#### Changes

* The function parser is now less strict when it comes to function names.

--------------------------------------------------------

### 5.1.1 <small> (2017-01-17)</small>

Update using ```composer update```.

#### Changes

* JSONP responses are now handled by the JSON response builder.

--------------------------------------------------------

### 5.1.0 <small> (2017-01-16)</small>

Update using ```composer update```.

#### New

* Added a optional ```NullableTrait``` to the ORM.
* Added ```Command::STATUS_SUCCESS``` constant.
* Added ```Command::STATUS_ERROR``` constant.
* Added ```cache.remove``` command.
* Added ```cache.clear``` command.
* Added ```application.base_url``` config key.
* Now possible to set middleware priority.
* The ORM now allows you to configure the foreign key name using the ```$foreignKeyName``` property.

#### Changes

* The response class will no longer auto render views. Views should be rendered in the controller.
* Removed the query convenience trait.
* Removed support for "piped" validation rules.
* New syntax for passing parameters to middleware.
* New syntax for passing parameters to validation rules.

#### Bugfixes

* ORM::getForeignKey() now uses Str::camel2underscored() instead of strtolower().

> Check out the upgrade guide for details on how to upgrade from ```5.0.*.```

--------------------------------------------------------

### 5.0.23 <small> (2017-01-01)</small>

Update using ```composer update```.

#### Bugfixes

* Query compiler will now properly escape JSON path segments.
* MySQL query compiler will now unquote extracted JSON values.

--------------------------------------------------------

### 5.0.22 <small>(2016-12-28)</small>

Update using ```composer update```.

#### New

* The query builder now supports set operations.
* Now possible to customize the width of progressbars.

#### Changes

* Deprecated the query convenience trait. It will be removed in Mako 5.1.0.

#### Bugfixes

* The image library will now show an error when trying to open a unsupported image type.

#### Improvements

* Various optimizations.

--------------------------------------------------------

### 5.0.21 <small>(2016-12-15)</small>

Update using ```composer update```.

#### New

* Now possible to return a status/exit code from reactor commands.

--------------------------------------------------------

### 5.0.20 <small>(2016-12-12)</small>

Update using ```composer update```.

#### Bugfixes

* Reverted breaking changes to compiled templates that were introduced in 5.0.17.

--------------------------------------------------------

### 5.0.19 <small>(2016-12-10)</small>

Update using ```composer update```.

#### Bugfixes

* CLI error handler will no longer fail when displaying a generic error message.

--------------------------------------------------------

### 5.0.18 <small>(2016-12-07)</small>

Update using ```composer update```.

#### New

* Now possible to update JSON values using the unified JSON query syntax.
* Now possible to bind parameters to raw SQL when using the query builder.

#### Improvements

* Various optimizations.

--------------------------------------------------------

### 5.0.17 <small>(2016-12-01)</small>

Update using ```composer update```.

#### New

* Now possible to access route parameters outside route actions.

#### Bugfixes

* Migration rollback now works as expected.

#### Improvements

* Various optimizations.

--------------------------------------------------------

### 5.0.16 <small>(2016-11-24)</small>

Update using ```composer update```.

#### Bugfixes

* Don't resolve singletons multiple times when using the container aware trait.

--------------------------------------------------------

### 5.0.15 <small>(2016-11-17)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed an issue where strict reactor commands would fail when called with a "global" option.

--------------------------------------------------------

### 5.0.14 <small>(2016-11-08)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed issue with ```Gatekeeper::forceLogin()```.

#### Improvements

* Error handler now supports ```xdebug.overload_var_dump```.

--------------------------------------------------------

### 5.0.13 <small>(2016-11-02)</small>

Update using ```composer update```.

#### Bugfixes

* ```ORM::toArray()``` will no longer try to convert ```false``` to an array.

--------------------------------------------------------

### 5.0.12 <small>(2016-11-01)</small>

Update using ```composer update```.

#### Bugfixes

* Corrected the return type of the ```View::assign()``` method.

--------------------------------------------------------

### 5.0.11 <small>(2016-10-14)</small>

Update using ```composer update```.

#### Bugfixes

* The ```$shouldTouchOnInsert```, ```$shouldTouchOnUpdate``` and ```$shouldTouchOnDelete``` properties of the ```TimestampedTrait``` now work as expected.

#### Improvements

* The redis client now supports dash-separated commands.
* Checking a ORM relation with ```isset()``` will now lazy load it if it hasn't already been loaded.

--------------------------------------------------------

### 5.0.10 <small>(2016-10-11)</small>

Update using ```composer update```.

#### Bugfixes

* The redis client will no longer assume that it has recieved the data it asked for.

--------------------------------------------------------

### 5.0.9 <small>(2016-10-11)</small>

Update using ```composer update```.

#### Bugfixes

* The Redis client now reads data in 4096 byte chunks to avoid issues with large values.

--------------------------------------------------------

### 5.0.8 <small>(2016-10-11)</small>

Update using ```composer update```.

#### Changes

* ```Request::file()``` now returns ```UploadedFile``` objects.

#### Bugfixes

* Redis cache store is now instantiated with the configured class whitelist.

--------------------------------------------------------

### 5.0.7 <small>(2016-10-08)</small>

Update using ```composer update```.

#### New

* Added ```Connection::yield()``` and ```Query::yield()``` methods that allow you to iterate over result sets using a generator.

--------------------------------------------------------

### 5.0.6 <small>(2016-10-06)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed  ```Query::first()``` fetch mode bug.

--------------------------------------------------------

### 5.0.5 <small>(2016-10-06)</small>

Update using ```composer update```.

#### Bugfixes

* Query pagination now works as expected with distinct selections.

#### Improvements

* ```Query::countDistinct()``` now supports an array of columns names.

--------------------------------------------------------

### 5.0.4 <small>(2016-10-05)</small>

Update using ```composer update```.

#### Changes

* Simplified stack trace for JSON error responses.

--------------------------------------------------------

### 5.0.3 <small>(2016-10-05)</small>

Update using ```composer update```.

#### Bugfixes

* Query pagination now works as expected with grouping.

--------------------------------------------------------

### 5.0.2 <small>(2016-10-05)</small>

Update using ```composer update```.

#### Bugfixes

* The output escaper now accepts null values.

--------------------------------------------------------

### 5.0.1 <small>(2016-10-05)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed validation bug.

--------------------------------------------------------

### 5.0.0 <small>(2016-10-04)</small>

Update using ```composer update```.

#### New

* The query builder now supports [row-level locking](:base_url:/docs/5.0/databases-sql:query-builder#row_level_locking).
* The query builder now has a [unified syntax for querying JSON fields](:base_url:/docs/5.0/databases-sql:query-builder#JSON_data).
* New and simplified [pagination functionality](:base_url:/docs/5.0/learn-more:pagination#usage_with_the_query_builder) when using the query builder.
* Added ```Query::havingRaw()``` method.
* Added ```Query::orHavingRaw()``` method.
* Added ```Query::columns()``` method.
* Added ```Query::countDistinct()``` method.
* Added support for [transaction savepoints](:base_url:/docs/5.0/databases-sql:basics#transactions:savepoints).
* Added ```Collection::extend()``` method.
* Added cluster support to the Redis client.
* Added IPv6 support to the Redis client.
* Added support for persistent connections to the Redis client.
* Now possible to define verbatim template blocks.
* Now possible to pass extra variables to included templates.
* Custom cache stores can be added using the ```CacheManager::extend()``` method.
* Custom encrypters can be added using the ```CryptoManager::extend()``` method.
* Added IPv4 and IPv6 utilities.
* You can now set a subnet when setting the IP adresses of trusted proxies.
* The character set will automatically be added to RSS and ATOM content-type headers.
* Added support for [contextual](:base_url:/docs/5.0/getting-started:dependency-injection#contextual_injection) dependency injection.
* You now have to whitelist the classes you want the framework to deserialize (cache and session stores).
* Added ```FileSystem::hash()``` method.
* Added ```FileSystem::hmac()``` method.
* Added ```app.generate_key``` command that can be used to generate secure encryption keys.
* Added unordered list CLI output [helper](:base_url:/docs/5.0/command-line:commands#output:helpers).
* Added ordered list CLI output [helper](:base_url:/docs/5.0/command-line:commands#output:helpers).
* Added ```Output::clear()``` [method](:base_url:/docs/5.0/command-line:commands#output:helpers).
* Reactor will now suggest a task or option name if an invalid one is used.
* Added support for [strict](:base_url:/docs/5.0/command-line:commands#basics:arguments-and-options) commands.
* Added FireTrait that makes it easier to [call a command from within a command](:base_url:/docs/5.0/command-line:commands#calling_commands_from_commands).

#### Changes

* ```Query::null()``` has been renamed to ```Query::isNull()```.
* ```Query::orNull()``` has been renamed to ```Query::orIsNull()```.
* ```Query::notNull()``` has been renamed to ```Query::isNotNull()```.
* ```Query::orNotNull()``` has been renamed to ```Query::orIsNotNull()```.
* ```Query::all()``` now returns a [result set](:base_url:/docs/5.0/databases-sql:query-builder#fetching_data) instead of an array.
* ORM read-only functionality is now handled using a trait.
* The ```ORM::isReadOnly()``` method has been removed.
* ORM records will no longer be made read-only when using joins.
* ORM values can now be [casted](:base_url:/5.0/databases-sql:orm#automatic_typecasting:scalars) to intergers using `int` instead of `integer`.
* ORM values can now be [casted](:base_url:/5.0/databases-sql:orm#automatic_typecasting:scalars) to booleans using `bool` instead of `boolean`.
* An exception will be thrown when trying to get a non-existing item from collection.
* The ```HTML::registerTag()``` method has been removed. Use ```HTML::extend()``` instead.
* Routing [middleware](:base_url:/docs/5.0/routing-and-controllers:routing#route_middleware) replaces route filters.
* The ```Routes::methods()``` method has been renamed to ```Routes::register()```.
* The ```Route::setNamespace()``` method has been renamed to ```Route::namespace()```.
* The ```Controller::beforeFilter()``` has been renamed to ```Controller::beforeAction()```.
* The ```Controller::afterFilter()``` has been renamed to ```Controller::afterAction()```.
* Custom view renderers must now be added using the ```ViewFactory::extend()``` method.
* Removed the ```APC``` and ```XCache``` cache stores.
* Removed the ```Response::file()``` method.
* Removed the ```Response::stream()``` method.
* Removed the ```Response::redirect()``` method .
* Removed the ```Response::back()``` method.
* Added a [ControllerHelperTrait](:base_url:/docs/5.0/routing-and-controllers:controllers#controller_helpers) with the following methods: ```fileResponse```, ```streamResponse```, ```redirectResponse```, ```jsonResponse``` and ```jsonpReponse```.
* Removed the MCRYPT encrypter.
* Removed the ```Crypto::encryptAndSign()``` and ```Crypto::validateAndDecrypt()``` methods. All encrypted data is now signed and validated by default.
* Renamed ```FileSystem::includeFile()``` to ```FileSystem::include()```.
* Renamed ```FileSystem::requireFile()``` to ```FileSystem::require()```.
* Renamed ```FileSystem::includeFileOnce()``` to ```FileSystem::includeOnce()```.
* Renamed ```FileSystem::requireFileOnce()``` to ```FileSystem::requireOnce()```.
* Renamed ```FileSystem::isDirectoryEmpty()``` to ```FileSystem::isEmpty()```.
* Renamed ```FileSystem::exists()``` to ```FileSystem::has()```.
* Renamed ```FileSystem::delete()``` to ```FileSystem::remove()```.
* Renamed ```FileSystem::getContents()``` to ```FileSystem::get()```.
* Renamed ```FileSystem::putContents()``` to ```FileSystem::put()```.
* Renamed ```FileSystem::prependContents()``` to ```FileSystem::prepend()```.
* Renamed ```FileSystem::appendContents()``` to ```FileSystem::append()```.
* Renamed ```FileSystem::truncateContents()``` to ```FileSystem::truncate()```.

#### Improvements

* Miscellaneous improvements and optimizations.

> Mako 5.0 is a major version update that contains a few minor breaking changes. Make sure to read the upgrade instructions!

--------------------------------------------------------

### 4.5.14 <small>(2016-08-30)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed the docblock return type for ```CacheManager::instance()```.

--------------------------------------------------------

### 4.5.13 <small>(2016-08-09)</small>

Update using ```composer update```.

#### Improvements

* ```Container::call()``` now supports function calls in addition to closure and method calls.

--------------------------------------------------------

### 4.5.12 <small>(2016-08-02)</small>

Update using ```composer update```.

#### Bugfixes

* ```URLBuilder::toRoute()``` will now allow falsy parameters (0, 0.0, '0').

--------------------------------------------------------

### 4.5.11 <small>(2016-06-29)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed a leap year related bug in the ```Time``` class.

#### Improvements

* Less restrictive version requirements of third party libraries.

--------------------------------------------------------

### 4.5.10 <small>(2016-02-03)</small>

Update using ```composer update```.

#### Improvements

* Cache will now throw an exception if the store is unavailable.

--------------------------------------------------------

### 4.5.9 <small>(2015-11-26)</small>

Update using ```composer update```.

#### Bugfixes

* ETag caching will now work as expected when using mod_deflate with Apache > 2.4.0.

#### Improvements

* Better support for routes containing multibyte characters.

--------------------------------------------------------

### 4.5.8 <small>(2015-11-17)</small>

Update using ```composer update```.

#### Improvements

* PHP7 compatibility.

--------------------------------------------------------

### 4.5.7 <small>(2015-11-04)</small>

Update using ```composer update```.

#### Bugfixes

* The query builder can now generate working SQLite queries with an ```IN``` clause where the values come from a subquery.
* The ```before``` and ```after``` validation filters will now work as expected.

#### Improvements

* The query builder now supports joins with nested conditions.

--------------------------------------------------------

### 4.3.5, 4.4.6 <small>(2015-11-04)</small>

Update using ```composer update```.

#### Bugfixes

* The ```before``` and ```after``` validation filters will now work as expected.

--------------------------------------------------------


### 4.5.6 <small>(2015-09-11)</small>

Update using ```composer update```.

#### Improvements

* Only include ```pages``` array in pagination data when ```max_page_links``` > 0.

--------------------------------------------------------

### 4.5.5 <small>(2015-07-08)</small>

Update using ```composer update```.

#### Bugfixes

* Clean URLs should now work as expected when using the local development server.

--------------------------------------------------------

### 4.5.4 <small>(2015-06-17)</small>

Update using ```composer update```.

#### Bugfixes

* The progress bar will no longer fail when ```0``` is passed as the item count.

#### Improvements

* Better parameter binding for prepared statements.

> This update requires you to change the data type of the ```users.banned``` and ```users.activated``` fields from ```SET``` to ```BOOL``` (or ```TINYINT(1)```).

--------------------------------------------------------

### 4.5.3 <small>(2015-05-07)</small>

Update using ```composer update```.

#### Changes

* The ```Pagination::paginate()``` method is now public.

--------------------------------------------------------

### 4.5.2 <small>(2015-04-24)</small>

Update using ```composer update```.

#### Bugfixes

* Eager loading criteria now work as expected when eager loading in chunks.

--------------------------------------------------------

### 4.5.1 <small>(2015-04-20)</small>

Update using ```composer update```.

#### Bugfixes

* Now possible to eager load more than 1000 unique ids when using SQLite and Oracle ([#151](https://github.com/mako-framework/framework/issues/151)).

--------------------------------------------------------

### 4.5.0 <small>(2015-04-15)</small>

Update using ```composer update```.

#### New

* Now possible to send multiple headers with the same field-name.
* Added ```Request::getRoute()``` method.
* Added ```Response::hasHeader()``` method.
* Added ```Response::hasCookie()``` method.
* Added ```Response::removeCookie()``` method.
* Added ```Image::getHeight()``` method.
* Added ```Image::getWidth()``` method.
* Added ```Image::getDimensions()``` method.
* Added brute force throttling to the Gatekeeper library.
* Added a command bus library [#138](https://github.com/mako-framework/framework/pull/138).
* New and improved event handler.

#### Changes

* ```Str::slug()``` will now encode non-ascii characters as recommened by [RFC-3986](http://www.rfc-archive.org/getrfc.php?rfc=3986).
* Minor changes in the application and package directory structures.
* Added brute force throttling settings to the ```app/config/gatekeeper.php``` configuration file.
* Added 3 new fields to the gatekeeper users table.

#### Improvements

* Now possible to select a custom set of columns through a many-to-many relation.
* Various optimizations.

> This release comes with a few minor breaking changes. Check out the migration guide [here](:base_url:/docs/4.5/getting-started:upgrading).

--------------------------------------------------------

### 4.4.5 <small>(2015-03-06)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed bug in ```app.routes``` command.

--------------------------------------------------------

### 4.3.4, 4.4.4 <small>(2015-02-19)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed language cache issue.

--------------------------------------------------------

### 4.4.3 <small>(2015-02-04)</small>

Update using ```composer update```.

#### Improvements

* ```Query::column()``` and ```Query::first()``` will now generate a more optimized query.

--------------------------------------------------------

### 4.4.2 <small>(2015-02-03)</small>

Update using ```composer update```.

#### Improvements

* The command line error handler will now include the error location in the output.

--------------------------------------------------------

### 4.4.1 <small>(2015-02-02)</small>

Update using ```composer update```.

#### New

* Added ```Output::setFormatter()``` method.
* Added ```Output::isMuted()``` method.

#### Bugfixes

* The redis client will no longer try to authenticate when no password is provided.

#### Improvements

* Controllers no longer need to extend the Mako base controller.
* Global reactor options are now sorted alphabetically.
* You can now separate package booting into ```core```, ```web``` and ```cli```.

> This update requires a [small change](https://github.com/mako-framework/app/blob/master/app/config/application.php#L188) to the ```app/config/application.php``` configuration file.

--------------------------------------------------------

### 4.4.0 <small>(2015-01-26)</small>

Update using ```composer update```.

#### New

* Brand new reactor command line tool.
* Added optional ```$column``` parameter to the ```Query::column()``` method.
* Added Mako core class.
* Added ```Password::needsRehash()``` method.
* Added ```Request::isSafe()``` method.
* Added ```Session::getToken()``` method.
* Added ```Session::regenerateToken()``` method.
* Added ```Session::validateToken()``` method.
* Added ```token``` validation rule.
* Gatekeeper will automatically rehash passwords if needed.
* Added ```attribute```, ```css```, ```url``` and  ```js``` escaping filters.
* Escape filters are now also available in plain PHP views.

#### Changes

* Moved ```init.php``` file from the framework core to the application.
* Removed the ```MAKO_VERSION``` constant (use ```Mako::VERSION``` instead).
* Removed the ```Password::isLegacyHash()``` method.
* Removed the ```$legacyCheck``` parameter from the ```Password::validate()``` method.
* Renamed ```Session::generateToken()``` method to ```Session::generateOneTimeToken```.
* Renamed ```Session::validateToken()``` method to ```Session::validateOneTimeToken```.
* Renamed ```token``` validation rule to ```one_time_token```.

> This release comes with a few minor breaking changes. Check out the migration guide [here](:base_url:/docs/4.4/getting-started:upgrading).

--------------------------------------------------------

### 4.0.11, 4.1.5, 4.2.3, 4.3.3 <small>(2015-01-19)</small>

Update using ```composer update```.

#### Bugfixes

* Gatekeeper will use the provided "auth_key" configuration value.

--------------------------------------------------------

### 4.3.2 <small>(2014-12-07)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed validation bug.

--------------------------------------------------------

### 4.3.1 <small>(2014-12-02)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed routing bug.

--------------------------------------------------------

### 4.3.0 <small>(2014-11-27)</small>

Update using ```composer update```.

#### New

* Added ```ViewFactory::exists()``` method.
* Views are now cascading. This means that you can override package views in your application.
* Language files are now cascading. This means that you can override package language files in your application.
* Mako now includes default 403, 404, 405 error views that can easily be overriden.
* The ORM will now also forward non-static calls to the query builder.
* Added ```Connection::table()``` convenience method.
* Added ```Container::call()```  method ([#116](https://github.com/mako-framework/framework/pull/116)).
* Route actions are now executed by the ```Container::call()``` method ([#118](https://github.com/mako-framework/framework/pull/118)).
* Route filters are now executed by the ```Container::call()``` method ([#119](https://github.com/mako-framework/framework/pull/119)).
* Added a session NULL store.

#### Changes

* Moved all http exceptions to the ```mako\http\exceptions``` namespace.
* Renamed the ```PageNotFoundException``` to ```NotFoundException```.
* Controllers, Tasks and Migrations now use the ```ContainerAwareTrait``` trait by default.
* The ```ORM::builder()``` method is now public.
* The ```Route::constraints()``` method has been renamed to ```Route::when()```.

> This release comes with a few minor breaking changes. Check out the migration guide [here](:base_url:/docs/4.3/getting-started:upgrading).

--------------------------------------------------------

### 4.0.10, 4.1.4, 4.2.2 <small>(2014-11-21)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed query builder bug.

--------------------------------------------------------

### 4.0.9, 4.1.3, 4.2.1 <small>(2014-11-14)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed MCrypt autoloading issue ([#120](https://github.com/mako-framework/framework/issues/120)).

--------------------------------------------------------

### 4.2.0 <small>(2014-09-26)</small>

Update using ```composer update```.

#### New

* Added ```Time::formatLocalized``` method.
* Added ```TimeZone``` class.
* Added a Stopwatch class ([#113](https://github.com/mako-framework/framework/pull/113)).
* Added support for nested template extension.
* Added optional migration descriptions.
* Added ```render``` shortcut method to the view factory class.
* It is now possible to configure Gatekeeper to identify users using their username instead of their email.

#### Changes

* The ```Time``` class has been moved to the to ```mako\chrono``` namespace.
* The ```locale``` config option has been removed. You now have to set the appropriate locale for each language instead.
* The ```ViewFactory::create``` method will now return an instance of ```mako\view\View``` instead of an implementation of ```mako\view\renderers\RendererInterface```.
* Moved ```app/routes.php``` to ```app/routing/routes.php```.
* Filters must now be defined in ```app/routing/filters.php```.
* You can now use class filters in addition to closures.
* The ```UrlBuilder::current``` method will now include the current query parameters by default.
* Default Mcrypt encryption mode changed from ECB to CBC.
* Removed the ```app/packages``` directory. Packages will now be installed in the packagist vendor directory.
* Removed the global helper functions. They have been replaced with a trait and a class (NamespacedFileLoaderTrait and ClassInspector).

> This release comes with a few minor breaking changes. Check out the migration guide [here](:base_url:/docs/4.2/getting-started:upgrading).

--------------------------------------------------------

### 4.1.2 <small>(2014-09-05)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed issue with date casting in the ORM.

--------------------------------------------------------

### 4.0.8, 4.1.1 <small>(2014-09-01)</small>

Update using ```composer update```.

#### Bugfixes

* Added missing returns in gatekeeper user implementation.

--------------------------------------------------------

### 4.1.0 <small>(2014-08-20)</small>

Update using ```composer update```.

#### New

* Added sepia filter to the image library.
* Added negate filter to the image library.
* Added pixelate filter to the image library.
* Added brightness adjustment to the image library.
* Added sharpening to the image library.
* Now possible to create and restore temporary snapshots when using the image library.
* Added support for language caching.
* Added Connection::isAlive() method.
* Added Connection::reconnect() method.
* Added Connection::beginTransaction() method.
* Added Connection::commitTransaction() method.
* Added Connection::rollBackTransaction() method.
* Added Connection::getTransactionNestingLevel() method.
* Added Connection::inTransaction() method.
* It is now possible to configure the ORM to automatically typecast values.
* PageNotFoundExceptions now includes the request method and path ([#108](https://github.com/mako-framework/framework/pull/108)).
* Now possible to register custom view renderers without having to implement a custom view factory.
* Added Application::isCommandLine() method.
* Added ErrorHandler::disableLoggingFor() method.

#### Changes

* The logger and errorHandler services are no longer required for an application to work.
* Removed the the ORM::$dateTimeColumms property. Use the new typecast feature instead.
* Selecting specific columns when using the ORM will no longer make the records read-only. Joining however, will do.

> You must also add the ```language_cache``` key to your [application configuration](https://github.com/mako-framework/app/blob/master/app/config/application.php#L61) file.

--------------------------------------------------------

### 4.0.7 <small>(2014-08-18)</small>

Update using ```composer update```.

#### New

* Now possible to configure the date output format when converting ORM records to array and/or json.

#### Bugfixes

* Escape exception message in debug template.

--------------------------------------------------------

### 4.0.6 <small>(2014-08-5)</small>

Update using ```composer update```.

#### Improvements

* Improved ORM::toArray() and ORM::toJson methods.

--------------------------------------------------------

### 4.0.5 <small>(2014-07-24)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed bug in the file based cache store.

--------------------------------------------------------

### 4.0.4 <small>(2014-07-04)</small>

Update using ```composer update```.

#### Bugfixes

* Image library now uses the correct image quality when saving.
* Image library watermarking now works as expected.

--------------------------------------------------------

### 4.0.3 <small>(2014-07-02)</small>

Update using ```composer update```.

#### Improvements

* The error handler is no longer loading external JavaScript libraries.

--------------------------------------------------------

### 4.0.2 <small>(2014-07-01)</small>

Update using ```composer update```.

#### Changes

* Namespaced the helper functions to avoild naming collisions with global functions.

#### Bugfixes

* Added ```mako\get_class_traits()``` helper function to improve detection of trait usage.

--------------------------------------------------------

### 4.0.1 <small>(2014-06-26)</small>

Update using ```composer update```.

#### Bugfixes

* Fixed bug where User::isMemberOf would return NULL if group id was used instead of group name.

--------------------------------------------------------


### 4.0.0 <small>(2014-06-26)</small>

Mako 4.0.0 is a complete rewrite where the main focus has been on improving testability, extensibility, security and the overall quality of the framework.

Mako 4 also includes a bunch of new features. Here are some of them:

* A new and improved RESTful routing system
* A brand new authentication library
* A smart and easy to use dependecy injection container
* Timestamped and OptimisticLocking traits for the ORM
* An image manipulation library that supports both GD and ImageMagick

[_Check out the documentation for the full list of changes_](:base_url:/docs/4.0).

> Note that this is ```NOT``` a one step upgrade but the API changes have been kept to a minimum so upgrading a project from Mako 3.6.x shouldn't pose too many problems.
