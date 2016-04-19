--TEST--
Basic features using BlackHole cache engine
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
Core::instance_stub(['cache_engine' => 'BlackHole']);
require __DIR__.'/_test.php';
?>
--EXPECT--
string(13) "initial state"
bool(true)
string(10) "simple set"
bool(true)
string(10) "simple get"
bool(false)
string(10) "simple del"
bool(true)
bool(false)
string(16) "set the same key"
bool(true)
string(34) "get non-existent key with callback"
string(2) "me"
string(26) "get non-existent key again"
bool(false)
string(18) "namespaced key set"
bool(true)
string(18) "namespaced key get"
bool(false)
string(18) "namespaced key del"
bool(true)
bool(false)
string(25) "namespaced key del parent"
bool(true)
bool(true)
bool(false)
string(19) "state after disable"
bool(false)
string(17) "get after disable"
bool(false)
string(31) "get with callback after disable"
int(5)
