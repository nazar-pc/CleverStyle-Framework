--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$User  = User::instance();

$group_1 = 99;
$group_2 = 100;

var_dump('Get groups');
$groups = $User->get_groups(User::ROOT_ID);
var_dump($groups);

var_dump('Add group');
var_dump($User->add_groups($group_1, User::ROOT_ID));
var_dump($User->get_groups(User::ROOT_ID));

var_dump('Delete group');
var_dump($User->del_groups($group_1, User::ROOT_ID));
var_dump($User->get_groups(User::ROOT_ID));

var_dump('Set groups');
var_dump($User->set_groups([$group_1, $group_2], User::ROOT_ID));
var_dump($User->get_groups(User::ROOT_ID));

$User->set_groups($groups, User::ROOT_ID);

var_dump('Add/set/get/del groups (guest user)');
var_dump($User->add_groups($group_1, User::GUEST_ID));
var_dump($User->set_groups([$group_1, $group_2], User::GUEST_ID));
var_dump($User->get_groups(User::GUEST_ID));
var_dump($User->del_groups($group_1, User::GUEST_ID));
?>
--EXPECT--
string(10) "Get groups"
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
string(9) "Add group"
bool(true)
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(99)
}
string(12) "Delete group"
bool(true)
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(2)
}
string(10) "Set groups"
bool(true)
array(2) {
  [0]=>
  int(99)
  [1]=>
  int(100)
}
string(35) "Add/set/get/del groups (guest user)"
bool(false)
bool(false)
bool(false)
bool(false)
