--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Group = Group::instance();
var_dump('all groups', $Group->get_all());
var_dump('group 1', $Group->get(1));
var_dump('group 1 and 2', $Group->get([1, 2]));
$id = $Group->add('Group 1', '');
var_dump('new group added', $id, $Group->get($id));
var_dump('group modification', $Group->set($id, 'Group 1', 'Description added'), $Group->get($id));
var_dump('group permissions', $Group->get_permissions($id));
var_dump('set group permissions', $Group->set_permissions([2 => 0], $id), $Group->get_permissions($id));
var_dump('reset modified group permissions', $Group->set_permissions([2 => -1], $id), $Group->get_permissions($id));
var_dump('set group permissions once again', $Group->set_permissions([2 => 0], $id), $Group->get_permissions($id));
var_dump('reset all group permissions', $Group->del_permissions_all($id), $Group->get_permissions($id));
$id = $Group->add('test', 'test');
var_dump('another group added', $id, $Group->get($id));
var_dump('delete group', $Group->del($id), $Group->get($id));
?>
--EXPECT--
string(10) "all groups"
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
string(7) "group 1"
array(3) {
  ["id"]=>
  int(1)
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
    int(1)
    ["title"]=>
    string(14) "Administrators"
    ["description"]=>
    string(14) "Administrators"
  }
  [1]=>
  array(3) {
    ["id"]=>
    int(2)
    ["title"]=>
    string(5) "Users"
    ["description"]=>
    string(5) "Users"
  }
}
string(15) "new group added"
int(3)
array(3) {
  ["id"]=>
  int(3)
  ["title"]=>
  string(7) "Group 1"
  ["description"]=>
  string(0) ""
}
string(18) "group modification"
bool(true)
array(3) {
  ["id"]=>
  int(3)
  ["title"]=>
  string(7) "Group 1"
  ["description"]=>
  string(17) "Description added"
}
string(17) "group permissions"
array(0) {
}
string(21) "set group permissions"
bool(true)
array(1) {
  [2]=>
  int(0)
}
string(32) "reset modified group permissions"
bool(true)
array(0) {
}
string(32) "set group permissions once again"
bool(true)
array(1) {
  [2]=>
  int(0)
}
string(27) "reset all group permissions"
bool(true)
array(0) {
}
string(19) "another group added"
int(4)
array(3) {
  ["id"]=>
  int(4)
  ["title"]=>
  string(4) "test"
  ["description"]=>
  string(4) "test"
}
string(12) "delete group"
bool(true)
bool(false)
