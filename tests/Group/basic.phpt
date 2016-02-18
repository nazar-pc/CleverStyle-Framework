--TEST--
Basic operations on groups
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Group = Group::instance();
var_dump('all groups', $Group->get_all());
var_dump('group 1', $Group->get(1));
var_dump('group 1 and 2', $Group->get([1, 2]));
$id = $Group->add('Group 1', '');
var_dump('new group added', $id);
if (!$id) {
  exit;
}
var_dump('group permissions', $Group->get_permissions($id));
var_dump(Permission::instance()->get_all());
var_dump('set group permissions', $Group->set_permissions([2 => 0], $id));
var_dump('check modified group permissions', $Group->get_permissions($id));
var_dump('reset modified group permissions', $Group->set_permissions([2 => -1], $id));
var_dump('check modified group permissions again', $Group->get_permissions($id));
?>
--EXPECT--
string(10) "all groups"
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
string(7) "group 1"
array(3) {
  ["id"]=>
  string(1) "1"
  ["title"]=>
  string(14) "Administrators"
  ["description"]=>
  string(14) "Administrators"
}
string(13) "group 1 and 2"
array(2) {
  [0]=>
  array(3) {
    ["id"]=>
    string(1) "1"
    ["title"]=>
    string(14) "Administrators"
    ["description"]=>
    string(14) "Administrators"
  }
  [1]=>
  array(3) {
    ["id"]=>
    string(1) "2"
    ["title"]=>
    string(5) "Users"
    ["description"]=>
    string(5) "Users"
  }
}
string(15) "new group added"
int(4)
string(17) "group permissions"
array(0) {
}
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
string(21) "set group permissions"
bool(true)
string(32) "check modified group permissions"
array(1) {
  [2]=>
  int(0)
}
string(32) "reset modified group permissions"
bool(true)
string(38) "check modified group permissions again"
array(0) {
}
