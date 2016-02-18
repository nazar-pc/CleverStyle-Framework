--TEST--
Basic operations on permissions
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Permission = Permission::instance();
var_dump('all permissions', $Permission->get_all());
var_dump('permission 1', $Permission->get(1));
var_dump('permissions by group api/System', $Permission->get(null, 'api/System'));
var_dump('permissions by label index', $Permission->get(null, null, 'index'));
var_dump('permissions by group api/System and label index', $Permission->get(null, 'api/System', 'index'));
var_dump('permissions by group api/System or label index', $Permission->get(null, 'api/System', 'index', 'or'));
$id = $Permission->add('test_permissions', 'first');
var_dump('new permission added', $id, $Permission->get($id));
var_dump('permission modification', $Permission->set($id, 'test_permissions', 'first_1'), $Permission->get($id));
var_dump('delete permission', $Permission->del($id), $Permission->get($id));
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
  string(1) "1"
  ["label"]=>
  string(5) "index"
  ["group"]=>
  string(12) "admin/System"
}
string(31) "permissions by group api/System"
array(1) {
  [0]=>
  array(3) {
    ["id"]=>
    string(1) "2"
    ["label"]=>
    string(5) "index"
    ["group"]=>
    string(10) "api/System"
  }
}
string(26) "permissions by label index"
array(2) {
  [0]=>
  array(3) {
    ["id"]=>
    string(1) "1"
    ["label"]=>
    string(5) "index"
    ["group"]=>
    string(12) "admin/System"
  }
  [1]=>
  array(3) {
    ["id"]=>
    string(1) "2"
    ["label"]=>
    string(5) "index"
    ["group"]=>
    string(10) "api/System"
  }
}
string(47) "permissions by group api/System and label index"
array(1) {
  [0]=>
  array(3) {
    ["id"]=>
    string(1) "2"
    ["label"]=>
    string(5) "index"
    ["group"]=>
    string(10) "api/System"
  }
}
string(46) "permissions by group api/System or label index"
array(2) {
  [0]=>
  array(3) {
    ["id"]=>
    string(1) "1"
    ["label"]=>
    string(5) "index"
    ["group"]=>
    string(12) "admin/System"
  }
  [1]=>
  array(3) {
    ["id"]=>
    string(1) "2"
    ["label"]=>
    string(5) "index"
    ["group"]=>
    string(10) "api/System"
  }
}
string(20) "new permission added"
int(3)
array(3) {
  ["id"]=>
  string(1) "3"
  ["label"]=>
  string(5) "first"
  ["group"]=>
  string(16) "test_permissions"
}
string(23) "permission modification"
bool(true)
array(3) {
  ["id"]=>
  string(1) "3"
  ["label"]=>
  string(7) "first_1"
  ["group"]=>
  string(16) "test_permissions"
}
string(17) "delete permission"
bool(true)
bool(false)
