--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Permission = Permission::instance();
var_dump('all permissions');
var_dump($Permission->get_all());

var_dump('permission 1');
var_dump($Permission->get(1));

var_dump('permissions by group api/System');
var_dump($Permission->get(null, 'api/System'));

var_dump('permissions by label index');
var_dump($Permission->get(null, null, 'index'));

var_dump('permissions by group api/System and label index');
var_dump($Permission->get(null, 'api/System', 'index'));

var_dump('new permission added');
$id = $Permission->add('test_permissions', 'first');
var_dump($id);
var_dump($Permission->get($id));

var_dump('permission modification');
var_dump($Permission->set($id, 'test_permissions', 'first_1'));
var_dump($Permission->get($id));

var_dump('delete permission');
var_dump($Permission->del($id));
var_dump($Permission->get($id));
?>
--EXPECT--
string(15) "all permissions"
array(2) {
  ["admin/System"]=>
  array(1) {
    ["index"]=>
    int(1)
  }
  ["api/System"]=>
  array(1) {
    ["index"]=>
    int(2)
  }
}
string(12) "permission 1"
array(3) {
  ["id"]=>
  int(1)
  ["group"]=>
  string(12) "admin/System"
  ["label"]=>
  string(5) "index"
}
string(31) "permissions by group api/System"
array(1) {
  [0]=>
  array(3) {
    ["id"]=>
    int(2)
    ["group"]=>
    string(10) "api/System"
    ["label"]=>
    string(5) "index"
  }
}
string(26) "permissions by label index"
array(2) {
  [0]=>
  array(3) {
    ["id"]=>
    int(1)
    ["group"]=>
    string(12) "admin/System"
    ["label"]=>
    string(5) "index"
  }
  [1]=>
  array(3) {
    ["id"]=>
    int(2)
    ["group"]=>
    string(10) "api/System"
    ["label"]=>
    string(5) "index"
  }
}
string(47) "permissions by group api/System and label index"
array(1) {
  [0]=>
  array(3) {
    ["id"]=>
    int(2)
    ["group"]=>
    string(10) "api/System"
    ["label"]=>
    string(5) "index"
  }
}
string(20) "new permission added"
int(3)
array(3) {
  ["id"]=>
  int(3)
  ["group"]=>
  string(16) "test_permissions"
  ["label"]=>
  string(5) "first"
}
string(23) "permission modification"
bool(true)
array(3) {
  ["id"]=>
  int(3)
  ["group"]=>
  string(16) "test_permissions"
  ["label"]=>
  string(7) "first_1"
}
string(17) "delete permission"
bool(true)
bool(false)
