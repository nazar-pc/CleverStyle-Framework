--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Session = Session::instance();

var_dump('Create session');
var_dump($Session->add(User::ROOT_ID));

var_dump('Get non-existent data');
var_dump($Session->get_data('xyz'));

var_dump('Set data');
var_dump($Session->set_data('xyz', ['whatever', false, null, 15]));

var_dump('Get data');
var_dump($Session->get_data('xyz'));

var_dump('Set data (other value)');
var_dump($Session->set_data('xyz', 'xyz'));

var_dump('Get data (other value)');
var_dump($Session->get_data('xyz'));

var_dump('Del data');
var_dump($Session->del_data('xyz'));
var_dump($Session->get_data('xyz'));

var_dump('Del data (already deleted)');
var_dump($Session->del_data('xyz'));

var_dump('Set data when session is not present yet');
Session::instance_reset();
unset(Request::instance()->cookie['session']);
$Session = Session::instance();
var_dump($Session->get_id());
var_dump($Session->set_data('xyz', 'xyz'));
var_dump($Session->get_id());
var_dump($Session->get_data('xyz'));
?>
--EXPECTF--
string(14) "Create session"
string(32) "%s"
string(21) "Get non-existent data"
bool(false)
string(8) "Set data"
bool(true)
string(8) "Get data"
array(4) {
  [0]=>
  string(8) "whatever"
  [1]=>
  bool(false)
  [2]=>
  NULL
  [3]=>
  int(15)
}
string(22) "Set data (other value)"
bool(true)
string(22) "Get data (other value)"
string(3) "xyz"
string(8) "Del data"
bool(true)
bool(false)
string(26) "Del data (already deleted)"
bool(true)
string(40) "Set data when session is not present yet"
bool(false)
bool(true)
string(32) "%s"
string(3) "xyz"
