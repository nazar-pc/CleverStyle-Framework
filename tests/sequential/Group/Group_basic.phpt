--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Group = Group::instance();
var_dump('All groups');
var_dump($Group->get_all());

var_dump('Group 1');
var_dump($Group->get(1));

var_dump('Group 1 and 2');
var_dump($Group->get([1, 2]));

var_dump('New group added');
$id = $Group->add('Group 1', '');
var_dump($id);
var_dump($Group->get($id));

var_dump('Group modification');
var_dump($Group->set($id, 'Group 1', 'Description added'));
var_dump($Group->get($id));

var_dump('Group permissions');
var_dump($Group->get_permissions($id));

var_dump('Set group permissions');
var_dump($Group->set_permissions([2 => 0], $id));
var_dump($Group->get_permissions($id));

var_dump('Reset modified group permissions');
var_dump($Group->set_permissions([2 => -1], $id));
var_dump($Group->get_permissions($id));

var_dump('Set group permissions once again');
var_dump($Group->set_permissions([2 => 0], $id));
var_dump($Group->get_permissions($id));

var_dump('Reset all group permissions');
var_dump($Group->del_permissions_all($id));
var_dump($Group->get_permissions($id));

var_dump('Another group added');
$id = $Group->add('test', 'test');
var_dump($id);
var_dump($Group->get($id));

var_dump('Delete group');
var_dump($Group->del($id));
var_dump($Group->get($id));

var_dump('Get non-existent group');
var_dump($Group->get(0));
var_dump($Group->get(999));
var_dump($Group->get([0, 1, 2, 999]));

var_dump('Delete multiple groups');
var_dump($id_1 = $Group->add('d1', ''));
var_dump($id_2 = $Group->add('d2', ''));
var_dump($Group->del([$id_1, $id_2]));
var_dump($Group->get([$id_1, $id_2]));

var_dump('Delete system groups');
var_dump($Group->del(User::ADMIN_GROUP_ID));
var_dump($Group->del(User::USER_GROUP_ID));

?>
--EXPECT--
string(10) "All groups"
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
string(7) "Group 1"
array(3) {
  ["id"]=>
  int(1)
  ["title"]=>
  string(14) "Administrators"
  ["description"]=>
  string(14) "Administrators"
}
string(13) "Group 1 and 2"
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
string(15) "New group added"
int(3)
array(3) {
  ["id"]=>
  int(3)
  ["title"]=>
  string(7) "Group 1"
  ["description"]=>
  string(0) ""
}
string(18) "Group modification"
bool(true)
array(3) {
  ["id"]=>
  int(3)
  ["title"]=>
  string(7) "Group 1"
  ["description"]=>
  string(17) "Description added"
}
string(17) "Group permissions"
array(0) {
}
string(21) "Set group permissions"
bool(true)
array(1) {
  [2]=>
  int(0)
}
string(32) "Reset modified group permissions"
bool(true)
array(0) {
}
string(32) "Set group permissions once again"
bool(true)
array(1) {
  [2]=>
  int(0)
}
string(27) "Reset all group permissions"
bool(true)
array(0) {
}
string(19) "Another group added"
int(4)
array(3) {
  ["id"]=>
  int(4)
  ["title"]=>
  string(4) "test"
  ["description"]=>
  string(4) "test"
}
string(12) "Delete group"
bool(true)
bool(false)
string(22) "Get non-existent group"
bool(false)
bool(false)
array(4) {
  [0]=>
  bool(false)
  [1]=>
  array(3) {
    ["id"]=>
    int(1)
    ["title"]=>
    string(14) "Administrators"
    ["description"]=>
    string(14) "Administrators"
  }
  [2]=>
  array(3) {
    ["id"]=>
    int(2)
    ["title"]=>
    string(5) "Users"
    ["description"]=>
    string(5) "Users"
  }
  [3]=>
  bool(false)
}
string(22) "Delete multiple groups"
int(5)
int(6)
bool(true)
array(2) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
}
string(20) "Delete system groups"
bool(false)
bool(false)
