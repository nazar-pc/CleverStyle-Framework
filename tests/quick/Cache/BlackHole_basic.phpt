--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
Core::instance_stub(['cache_engine' => 'BlackHole']);
require __DIR__.'/_test.php';
?>
--EXPECT--
string(13) "Initial state"
bool(true)
string(3) "Set"
bool(true)
string(3) "Get"
bool(false)
string(3) "Del"
bool(true)
bool(false)
string(17) "Set (as property)"
string(17) "Get (as property)"
bool(false)
string(17) "Del (as property)"
bool(false)
string(16) "Set the same key"
bool(true)
string(34) "Get non-existent key with callback"
string(2) "me"
string(26) "Get non-existent key again"
bool(false)
string(18) "Namespaced key set"
bool(true)
string(18) "Namespaced key get"
bool(false)
string(18) "Namespaced key del"
bool(true)
bool(false)
string(25) "Namespaced key del parent"
bool(true)
bool(true)
bool(false)
string(33) "Namespaced (using prefix) key set"
bool(true)
string(33) "Namespaced (using prefix) key get"
bool(false)
string(33) "Namespaced (using prefix) key del"
bool(true)
bool(false)
string(40) "Namespaced (using prefix) key del parent"
bool(true)
bool(true)
bool(false)
string(46) "Namespaced (using prefix, as property) key set"
string(46) "Namespaced (using prefix, as property) key get"
bool(false)
string(46) "Namespaced (using prefix, as property) key del"
bool(false)
string(53) "Namespaced (using prefix, as property) key del parent"
bool(false)
string(15) "Get after clean"
bool(true)
bool(false)
string(19) "State after disable"
bool(false)
string(17) "Get after disable"
bool(false)
string(31) "Get with callback after disable"
int(5)
