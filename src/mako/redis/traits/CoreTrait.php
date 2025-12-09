<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\redis\traits;

use Deprecated;

/**
 * Redis core commands.
 *
 * @see https://redis.io/docs/latest/commands/
 */
trait CoreTrait
{
	abstract protected function buildAndSendCommandAndReturnResponse(array $command, array $arguments = []): mixed;

	// Bitmap

	public function bitCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BITCOUNT'], $arguments);
	}

	public function bitField(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BITFIELD'], $arguments);
	}

	public function bitFieldRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BITFIELD_RO'], $arguments);
	}

	public function bitOp(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BITOP'], $arguments);
	}

	public function bitPos(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BITPOS'], $arguments);
	}

	public function getBit(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GETBIT'], $arguments);
	}

	public function setBit(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SETBIT'], $arguments);
	}

	// Cluster management

	public function asking(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ASKING'], $arguments);
	}

	public function clusterAddSlots(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'ADDSLOTS'], $arguments);
	}

	public function clusterAddSlotsRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'ADDSLOTSRANGE'], $arguments);
	}

	public function clusterBumpEpoch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'BUMPEPOCH'], $arguments);
	}

	public function clusterCountFailureReports(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'COUNT-FAILURE-REPORTS'], $arguments);
	}

	public function clusterCountKeysInSlot(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'COUNTKEYSINSLOT'], $arguments);
	}

	public function clusterDelSlots(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'DELSLOTS'], $arguments);
	}

	public function clusterDelSlotsRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'DELSLOTSRANGE'], $arguments);
	}

	public function clusterFailover(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'FAILOVER'], $arguments);
	}

	public function clusterFlushSlots(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'FLUSHSLOTS'], $arguments);
	}

	public function clusterForget(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'FORGET'], $arguments);
	}

	public function clusterGetKeysInSlot(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'GETKEYSINSLOT'], $arguments);
	}

	public function clusterInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'INFO'], $arguments);
	}

	public function clusterKeySlot(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'KEYSLOT'], $arguments);
	}

	public function clusterLinks(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'LINKS'], $arguments);
	}

	public function clusterMeet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'MEET'], $arguments);
	}

	public function clusterMyId(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'MYID'], $arguments);
	}

	public function clusterMyShardId(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'MYSHARDID'], $arguments);
	}

	public function clusterNodes(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'NODES'], $arguments);
	}

	public function clusterReplicas(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'REPLICAS'], $arguments);
	}

	public function clusterReplicate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'REPLICATE'], $arguments);
	}

	public function clusterReset(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'RESET'], $arguments);
	}

	public function clusterSaveConfig(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SAVECONFIG'], $arguments);
	}

	public function clusterSetConfigEpoch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SET-CONFIG-EPOCH'], $arguments);
	}

	public function clusterSetSlot(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SETSLOT'], $arguments);
	}

	public function clusterSetShards(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SETSHARDS'], $arguments);
	}

	public function clusterShards(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SHARDS'], $arguments);
	}

	#[Deprecated('use the "clusterReplicas" method instead', since: 'Redis 5.0.0')]
	public function clusterSlaves(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SLAVES'], $arguments);
	}

	#[Deprecated('use the "clusterShards" method instead', since: 'Redis 7.0.0')]
	public function clusterSlots(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLUSTER', 'SLOTS'], $arguments);
	}

	public function clusterReadonly(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['READONLY'], $arguments);
	}

	public function clusterReadwrite(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['READWRITE'], $arguments);
	}

	// Connection management

	public function auth(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['AUTH'], $arguments);
	}

	public function clientCaching(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'CACHING'], $arguments);
	}

	public function clientGetName(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'GETNAME'], $arguments);
	}

	public function clientGetRedir(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'GETREDIR'], $arguments);
	}

	public function clientId(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'ID'], $arguments);
	}

	public function clientInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'INFO'], $arguments);
	}

	public function clientKill(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'KILL'], $arguments);
	}

	public function clientList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'LIST'], $arguments);
	}

	public function clientNoEvict(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'NO-EVICT'], $arguments);
	}

	public function clientNoTouch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'NO-TOUCH'], $arguments);
	}

	public function clientPause(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'PAUSE'], $arguments);
	}

	public function clientReply(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'REPLY'], $arguments);
	}

	public function clientSetInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'SETINFO'], $arguments);
	}

	public function clientSetName(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'SETNAME'], $arguments);
	}

	public function clientTracking(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'TRACKING'], $arguments);
	}

	public function clientTrackingInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'TRACKINGINFO'], $arguments);
	}

	public function clientUnblock(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'UNBLOCK'], $arguments);
	}

	public function clientUnpause(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CLIENT', 'UNPAUSE'], $arguments);
	}

	public function echo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ECHO'], $arguments);
	}

	public function hello(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HELLO'], $arguments);
	}

	public function ping(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PING'], $arguments);
	}

	#[Deprecated('it can be replaced by just closing the connection', since: 'Redis 7.2.0')]
	public function quit(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['QUIT'], $arguments);
	}

	public function reset(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RESET'], $arguments);
	}

	public function select(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SELECT'], $arguments);
	}

	// Generic

	public function copy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COPY'], $arguments);
	}

	public function del(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['DEL'], $arguments);
	}

	public function dump(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['DUMP'], $arguments);
	}

	public function exists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EXISTS'], $arguments);
	}

	public function expire(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EXPIRE'], $arguments);
	}

	public function expireAt(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EXPIREAT'], $arguments);
	}

	public function expireTime(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EXPIRETIME'], $arguments);
	}

	public function keys(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['KEYS'], $arguments);
	}

	public function migrate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MIGRATE'], $arguments);
	}

	public function move(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MOVE'], $arguments);
	}

	public function objectEncoding(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['OBJECT', 'ENCODING'], $arguments);
	}

	public function objectFreq(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['OBJECT', 'FREQ'], $arguments);
	}

	public function objectIdleTime(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['OBJECT', 'IDLETIME'], $arguments);
	}

	public function objectRefCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['OBJECT', 'REFCOUNT'], $arguments);
	}

	public function persist(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PERSIST'], $arguments);
	}

	public function pExpire(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PEXPIRE'], $arguments);
	}

	public function pExpireAt(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PEXPIREAT'], $arguments);
	}

	public function pExpireTime(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PEXPIRETIME'], $arguments);
	}

	public function pTtl(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PTTL'], $arguments);
	}

	public function randomKey(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RANDOMKEY'], $arguments);
	}

	public function rename(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RENAME'], $arguments);
	}

	public function renameNx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RENAMENX'], $arguments);
	}

	public function restore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RESTORE'], $arguments);
	}

	public function scan(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCAN'], $arguments);
	}

	public function sort(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SORT'], $arguments);
	}

	public function sortRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SORT_RO'], $arguments);
	}

	public function touch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TOUCH'], $arguments);
	}

	public function ttl(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TTL'], $arguments);
	}

	public function type(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TYPE'], $arguments);
	}

	public function unlink(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['UNLINK'], $arguments);
	}

	public function wait(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['WAIT'], $arguments);
	}

	public function waitAof(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['WAITAOF'], $arguments);
	}

	// Geospatial indices

	public function geoAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEOADD'], $arguments);
	}

	public function geoDist(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEODIST'], $arguments);
	}

	public function geoHash(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEOHASH'], $arguments);
	}

	public function geoPos(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEOPOS'], $arguments);
	}

	#[Deprecated('it can be replaced by the "geoSearch" and "geoSearchStore" methods with the BYRADIUS argument', since: 'Redis 6.2.0')]
	public function geoRadius(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEORADIUS'], $arguments);
	}

	#[Deprecated('it can be replaced by the "geoSearch" method with the BYRADIUS argument', since: 'Redis 6.2.0')]
	public function getRadiusRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEORADIUS_RO'], $arguments);
	}

	#[Deprecated('it can be replaced by the "geoSearch" and "geoSearchStore" methods with the BYRADIUS and FROMMEMBER arguments', since: 'Redis 6.2.0')]
	public function geoRadiusByMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEORADIUSBYMEMBER'], $arguments);
	}

	#[Deprecated('it can be replaced by the "geoSearch" method with the BYRADIUS and FROMMEMBER arguments', since: 'Redis 6.2.0')]
	public function getRadiusByMemberRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEORADIUSBYMEMBER_RO'], $arguments);
	}

	public function geoSearch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEOSEARCH'], $arguments);
	}

	public function geoSearchStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GEOSEARCHSTORE'], $arguments);
	}

	// Hash

	public function hDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HDEL'], $arguments);
	}

	public function hExists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HEXISTS'], $arguments);
	}

	public function hExpire(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HEXPIRE'], $arguments);
	}

	public function hExpireAt(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HEXPIREAT'], $arguments);
	}

	public function hExpireTime(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HEXPIRETIME'], $arguments);
	}

	public function hGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HGET'], $arguments);
	}

	public function hGetAll(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HGETALL'], $arguments);
	}

	public function hGetDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HGETDEL'], $arguments);
	}

	public function hGetEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HGETEX'], $arguments);
	}

	public function hIncrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HINCRBY'], $arguments);
	}

	public function hIncrByFloat(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HINCRBYFLOAT'], $arguments);
	}

	public function hKeys(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HKEYS'], $arguments);
	}

	public function hLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HLEN'], $arguments);
	}

	public function hMGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HMGET'], $arguments);
	}

	#[Deprecated('it can be replaced by the "hSet" method with multiple field-value pairs', since: 'Redis 4.0.0')]
	public function hMSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HMSET'], $arguments);
	}

	public function hPersist(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HPERSIST'], $arguments);
	}

	public function hPExpire(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HPEXPIRE'], $arguments);
	}

	public function hPExpireAt(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HPEXPIREAT'], $arguments);
	}

	public function hPExpireTime(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HPEXPIRETIME'], $arguments);
	}

	public function hPTtl(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HPTTL'], $arguments);
	}

	public function hRandField(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HRANDFIELD'], $arguments);
	}

	public function hScan(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HSCAN'], $arguments);
	}

	public function hSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HSET'], $arguments);
	}

	public function hSetEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HSETEX'], $arguments);
	}

	public function hSetNx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HSETNX'], $arguments);
	}

	public function hStrLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HSTRLEN'], $arguments);
	}

	public function hTtl(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HTTL'], $arguments);
	}

	public function hVals(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['HVALS'], $arguments);
	}

	// HyperLogLog

	public function pfAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PFADD'], $arguments);
	}

	public function pfCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PFCOUNT'], $arguments);
	}

	public function pfDebug(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PFDEBUG'], $arguments);
	}

	public function pfMerge(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PFMERGE'], $arguments);
	}

	public function pfSelfTest(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PFSELFTEST'], $arguments);
	}

	// List

	public function blMove(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BLMOVE'], $arguments);
	}

	public function blMPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BLMPOP'], $arguments);
	}

	public function blPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BLPOP'], $arguments);
	}

	public function brPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BRPOP'], $arguments);
	}

	#[Deprecated('it can be replaced by the "blMove" method with the RIGHT and LEFT arguments', since: 'Redis 6.2.0')]
	public function brPopLPush(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BRPOPLPUSH'], $arguments);
	}

	public function lIndex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LINDEX'], $arguments);
	}

	public function lInsert(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LINSERT'], $arguments);
	}

	public function lLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LLEN'], $arguments);
	}

	public function lMove(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LMOVE'], $arguments);
	}

	public function lMPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LMPOP'], $arguments);
	}

	public function lPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LPOP'], $arguments);
	}

	public function lPos(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LPOS'], $arguments);
	}

	public function lPush(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LPUSH'], $arguments);
	}

	public function lPushX(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LPUSHX'], $arguments);
	}

	public function lRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LRANGE'], $arguments);
	}

	public function lRem(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LREM'], $arguments);
	}

	public function lSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LSET'], $arguments);
	}

	public function lTrim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LTRIM'], $arguments);
	}

	public function rPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RPOP'], $arguments);
	}

	#[Deprecated('it can be replaced by the "lMove" method with the RIGHT and LEFT arguments', since: 'Redis 6.2.0')]
	public function rPopLPush(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RPOPLPUSH'], $arguments);
	}

	public function rPush(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RPUSH'], $arguments);
	}

	public function rPushX(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RPUSHX'], $arguments);
	}

	// Scripting and functions

	public function eval(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EVAL'], $arguments);
	}

	public function evalRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EVAL_RO'], $arguments);
	}

	public function evalSha(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EVALSHA'], $arguments);
	}

	public function evalShaRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EVALSHA_RO'], $arguments);
	}

	public function fCall(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FCALL'], $arguments);
	}

	public function fCallRo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FCALL_RO'], $arguments);
	}

	public function functionDelete(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'DELETE'], $arguments);
	}

	public function functionDump(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'DUMP'], $arguments);
	}

	public function functionFlush(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'FLUSH'], $arguments);
	}

	public function functionKill(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'KILL'], $arguments);
	}

	public function functionList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'LIST'], $arguments);
	}

	public function functionLoad(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'LOAD'], $arguments);
	}

	public function functionRestore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'RESTORE'], $arguments);
	}

	public function functionStats(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FUNCTION', 'STATS'], $arguments);
	}

	public function scriptDebug(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCRIPT', 'DEBUG'], $arguments);
	}

	public function scriptExists(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCRIPT', 'EXISTS'], $arguments);
	}

	public function scriptFlush(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCRIPT', 'FLUSH'], $arguments);
	}

	public function scriptKill(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCRIPT', 'KILL'], $arguments);
	}

	public function scriptLoad(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCRIPT', 'LOAD'], $arguments);
	}

	// Server management

	public function aclCat(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'CAT'], $arguments);
	}

	public function aclDelUser(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'DELUSER'], $arguments);
	}

	public function aclDryRun(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'DRY-RUN'], $arguments);
	}

	public function aclGenPass(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'GENPASS'], $arguments);
	}

	public function aclGetUser(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'GETUSER'], $arguments);
	}

	public function aclList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'LIST'], $arguments);
	}

	public function aclLoad(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'LOAD'], $arguments);
	}

	public function aclLog(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'LOG'], $arguments);
	}

	public function aclSave(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'SAVE'], $arguments);
	}

	public function aclSetUser(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'SETUSER'], $arguments);
	}

	public function aclUsers(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'USERS'], $arguments);
	}

	public function aclWhoAmI(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ACL', 'WHOAMI'], $arguments);
	}

	public function bgRewriteAof(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BGREWRITEAOF'], $arguments);
	}

	public function bgSave(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BGSAVE'], $arguments);
	}

	public function command(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND'], $arguments);
	}

	public function commandCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND', 'COUNT'], $arguments);
	}

	public function commandDocs(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND', 'DOCS'], $arguments);
	}

	public function commandGetKeys(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND', 'GETKEYS'], $arguments);
	}

	public function commandGetKeysAndFlags(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND', 'GETKEYSANDFLAGS'], $arguments);
	}

	public function commandInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND', 'INFO'], $arguments);
	}

	public function commandList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['COMMAND', 'LIST'], $arguments);
	}

	public function configGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CONFIG', 'GET'], $arguments);
	}

	public function configResetStat(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CONFIG', 'RESETSTAT'], $arguments);
	}

	public function configRewrite(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CONFIG', 'REWRITE'], $arguments);
	}

	public function configSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['CONFIG', 'SET'], $arguments);
	}

	public function dbsize(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['DBSIZE'], $arguments);
	}

	public function failover(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FAILOVER'], $arguments);
	}

	public function flushAll(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FLUSHALL'], $arguments);
	}

	public function flushDb(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['FLUSHDB'], $arguments);
	}

	public function info(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['INFO'], $arguments);
	}

	public function lastSave(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LASTSAVE'], $arguments);
	}

	public function latencyDoctor(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LATENCY', 'DOCTOR'], $arguments);
	}

	public function latencyGraph(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LATENCY', 'GRAPH'], $arguments);
	}

	public function latencyHistogram(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LATENCY', 'HISTOGRAM'], $arguments);
	}

	public function latencyHistory(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LATENCY', 'HISTORY'], $arguments);
	}

	public function latencyLatest(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LATENCY', 'LATEST'], $arguments);
	}

	public function latencyReset(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LATENCY', 'RESET'], $arguments);
	}

	public function lolWut(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LOLWUT'], $arguments);
	}

	public function memoryDoctor(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MEMORY', 'DOCTOR'], $arguments);
	}

	public function memoryMallocStats(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MEMORY', 'MALLOC-STATS'], $arguments);
	}

	public function memoryPurge(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MEMORY', 'PURGE'], $arguments);
	}

	public function memoryStats(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MEMORY', 'STATS'], $arguments);
	}

	public function memoryUsage(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MEMORY', 'USAGE'], $arguments);
	}

	public function moduleList(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MODULE', 'LIST'], $arguments);
	}

	public function moduleLoad(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MODULE', 'LOAD'], $arguments);
	}

	public function moduleLoadEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MODULE', 'LOADEX'], $arguments);
	}

	public function moduleUnload(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MODULE', 'UNLOAD'], $arguments);
	}

	public function psync(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PSYNC'], $arguments);
	}

	public function replConf(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['REPLCONF'], $arguments);
	}

	public function replicaOf(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['REPLICAOF'], $arguments);
	}

	public function restoreAsking(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['RESTORE-ASKING'], $arguments);
	}

	public function role(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ROLE'], $arguments);
	}

	public function save(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SAVE'], $arguments);
	}

	public function shutdown(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SHUTDOWN'], $arguments);
	}

	#[Deprecated('it can be replaced by the "replicaOf" method', since: 'Redis 5.0.0')]
	public function slaveOf(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SLAVEOF'], $arguments);
	}

	public function slowLogGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SLOWLOG', 'GET'], $arguments);
	}

	public function slowLogLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SLOWLOG', 'LEN'], $arguments);
	}

	public function slowLogReset(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SLOWLOG', 'RESET'], $arguments);
	}

	public function swapDb(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SWAPDB'], $arguments);
	}

	public function sync(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SYNC'], $arguments);
	}

	public function time(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['TIME'], $arguments);
	}

	// Set

	public function sAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SADD'], $arguments);
	}

	public function sCard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SCARD'], $arguments);
	}

	public function sDiff(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SDIFF'], $arguments);
	}

	public function sDiffStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SDIFFSTORE'], $arguments);
	}

	public function sInter(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SINTER'], $arguments);
	}

	public function sInterCard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SINTERCARD'], $arguments);
	}

	public function sInterStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SINTERSTORE'], $arguments);
	}

	public function sIsMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SISMEMBER'], $arguments);
	}

	public function sMembers(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SMEMBERS'], $arguments);
	}

	public function sMIsMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SMISMEMBER'], $arguments);
	}

	public function sMove(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SMOVE'], $arguments);
	}

	public function sPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SPOP'], $arguments);
	}

	public function sRandMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SRANDMEMBER'], $arguments);
	}

	public function sRem(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SREM'], $arguments);
	}

	public function sScan(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SSCAN'], $arguments);
	}

	public function sUnion(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SUNION'], $arguments);
	}

	public function sUnionStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SUNIONSTORE'], $arguments);
	}

	// Sorted set

	public function bzMPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BZMPOP'], $arguments);
	}

	public function bzPopMax(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BZPOPMAX'], $arguments);
	}

	public function bzPopMin(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['BZPOPMIN'], $arguments);
	}

	public function zAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZADD'], $arguments);
	}

	public function zCard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZCARD'], $arguments);
	}

	public function zCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZCOUNT'], $arguments);
	}

	public function zDiff(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZDIFF'], $arguments);
	}

	public function zDiffStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZDIFFSTORE'], $arguments);
	}

	public function zIncrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZINCRBY'], $arguments);
	}

	public function zInter(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZINTER'], $arguments);
	}

	public function zInterCard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZINTERCARD'], $arguments);
	}

	public function zInterStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZINTERSTORE'], $arguments);
	}

	public function zLexCount(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZLEXCOUNT'], $arguments);
	}

	public function zMPop(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZMPOP'], $arguments);
	}

	public function zMScore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZMSCORE'], $arguments);
	}

	public function zPopMax(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZPOPMAX'], $arguments);
	}

	public function zPopMin(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZPOPMIN'], $arguments);
	}

	public function zRandMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZRANDMEMBER'], $arguments);
	}

	public function zRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZRANGE'], $arguments);
	}

	#[Deprecated('it can be replaced by the "zRange" method with the BYLEX argument', since: 'Redis 6.2.0')]
	public function zRangeByLex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZRANGEBYLEX'], $arguments);
	}

	#[Deprecated('it can be replaced by the "zRange" method with the BYSCORE argument', since: 'Redis 6.2.0')]
	public function zRangeByScore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZRANGEBYSCORE'], $arguments);
	}

	public function zRangeStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZRANGESTORE'], $arguments);
	}

	public function zRank(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZRANK'], $arguments);
	}

	public function zRem(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREM'], $arguments);
	}

	public function zRemRangeByLex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREMRANGEBYLEX'], $arguments);
	}

	public function zRemRangeByRank(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREMRANGEBYRANK'], $arguments);
	}

	public function zRemRangeByScore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREMRANGEBYSCORE'], $arguments);
	}

	#[Deprecated('it can be replaced by the "zRange" method with the REV argument', since: 'Redis 6.2.0')]
	public function zRevRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREVRANGE'], $arguments);
	}

	#[Deprecated('it can be replaced by the "zRange" method with the REV and BYLEX arguments', since: 'Redis 6.2.0')]
	public function zRevRangeByLex(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREVRANGEBYLEX'], $arguments);
	}

	#[Deprecated('it can be replaced by the "zRange" method with the REV and BYSCORE arguments', since: 'Redis 6.2.0')]
	public function zRevRangeByScore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREVRANGEBYSCORE'], $arguments);
	}

	public function zRevRank(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZREVRANK'], $arguments);
	}

	public function zScan(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZSCAN'], $arguments);
	}

	public function zScore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZSCORE'], $arguments);
	}

	public function zUnion(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZUNION'], $arguments);
	}

	public function zUnionStore(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['ZUNIONSTORE'], $arguments);
	}

	// Stream

	public function xAck(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XACK'], $arguments);
	}

	public function xAckDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XACKDEL'], $arguments);
	}

	public function xAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XADD'], $arguments);
	}

	public function xAutoClaim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XAUTOCLAIM'], $arguments);
	}

	public function xClaim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XCLAIM'], $arguments);
	}

	public function xDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XDEL'], $arguments);
	}

	public function xDelEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XDELEX'], $arguments);
	}

	public function xGroupCreate(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XGROUP', 'CREATE'], $arguments);
	}

	public function xGroupCreateConsumer(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XGROUP', 'CREATECONSUMER'], $arguments);
	}

	public function xGroupDelConsumer(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XGROUP', 'DELCONSUMER'], $arguments);
	}

	public function xGroupDestroy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XGROUP', 'DESTROY'], $arguments);
	}

	public function xGroupSetId(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XGROUP', 'SETID'], $arguments);
	}

	public function xInfoConsumers(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XINFO', 'CONSUMERS'], $arguments);
	}

	public function xInfoGroups(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XINFO', 'GROUPS'], $arguments);
	}

	public function xInfoStream(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XINFO', 'STREAM'], $arguments);
	}

	public function xLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XLEN'], $arguments);
	}

	public function xPending(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XPENDING'], $arguments);
	}

	public function xRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XRANGE'], $arguments);
	}

	public function xRead(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XREAD'], $arguments);
	}

	public function xReadGroup(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XREADGROUP'], $arguments);
	}

	public function xRevRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XREVRANGE'], $arguments);
	}

	public function xSetId(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XSETID'], $arguments);
	}

	public function xTrim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['XTRIM'], $arguments);
	}

	// String

	public function append(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['APPEND'], $arguments);
	}

	public function decr(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['DECR'], $arguments);
	}

	public function decrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['DECRBY'], $arguments);
	}

	public function get(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GET'], $arguments);
	}

	public function getDel(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GETDEL'], $arguments);
	}

	public function getEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GETEX'], $arguments);
	}

	public function getRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GETRANGE'], $arguments);
	}

	#[Deprecated('it can be replaced by the "set" method with the GET argument', since: 'Redis 6.2.0')]
	public function getSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['GETSET'], $arguments);
	}

	public function incr(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['INCR'], $arguments);
	}

	public function incrBy(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['INCRBY'], $arguments);
	}

	public function incrByFloat(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['INCRBYFLOAT'], $arguments);
	}

	public function lcs(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['LCS'], $arguments);
	}

	public function mGet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MGET'], $arguments);
	}

	public function mSet(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MSET'], $arguments);
	}

	public function mSetNx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MSETNX'], $arguments);
	}

	#[Deprecated('it can be replaced by the "set" method with the PX argument', since: 'Redis 2.6.12')]
	public function pSetEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['PSETEX'], $arguments);
	}

	public function set(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SET'], $arguments);
	}

	#[Deprecated('it can be replaced by the "set" method with the EX argument', since: 'Redis 2.6.12')]
	public function setEx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SETEX'], $arguments);
	}

	#[Deprecated('it can be replaced by the "set" method with the NX argument', since: 'Redis 2.6.12')]
	public function setNx(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SETNX'], $arguments);
	}

	public function setRange(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SETRANGE'], $arguments);
	}

	public function strLen(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['STRLEN'], $arguments);
	}

	#[Deprecated('it can be replaced by the "getRange" method', since: 'Redis 2.0.0')]
	public function subStr(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['SUBSTR'], $arguments);
	}

	// Transactions

	public function discard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['DISCARD'], $arguments);
	}

	public function exec(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['EXEC'], $arguments);
	}

	public function multi(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['MULTI'], $arguments);
	}

	public function unwatch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['UNWATCH'], $arguments);
	}

	public function watch(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['WATCH'], $arguments);
	}

	// Vector set

	public function vAdd(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VADD'], $arguments);
	}

	public function vCard(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VCARD'], $arguments);
	}

	public function vDim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VDIM'], $arguments);
	}

	public function vEmb(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VEMB'], $arguments);
	}

	public function vGetAttr(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VGETATTR'], $arguments);
	}

	public function vInfo(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VINFO'], $arguments);
	}

	public function vIsMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VISMEMBER'], $arguments);
	}

	public function vLinks(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VLINKS'], $arguments);
	}

	public function vRandMember(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VRANDMEMBER'], $arguments);
	}

	public function vRem(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VREM'], $arguments);
	}

	public function vSetAttr(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VSETATTR'], $arguments);
	}

	public function vSim(...$arguments): mixed
	{
		return $this->buildAndSendCommandAndReturnResponse(['VSIM'], $arguments);
	}
}
