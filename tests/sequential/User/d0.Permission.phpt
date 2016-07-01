--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Group      = Group::instance();
$Permission = Permission::instance();
$User       = User::instance();

var_dump('Prepare groups');
$group_1 = $Group->add('User permission testing 1', '');
$group_2 = $Group->add('User permission testing 2', '');
var_dump((bool)$group_1, (bool)$group_2);

var_dump('Prepare permissions');
$permission_1_group = 'User permission testing 1';
$permission_1       = $Permission->add($permission_1_group, $permission_1_group);
$permission_2_group = 'User permission testing 2';
$permission_2       = $Permission->add($permission_2_group, $permission_2_group);

var_dump('Prepare user');
$user_id = $User->registration('p1@test.com', false, true)['id'];
var_dump((bool)$user_id);

var_dump('Get permission (not set)');
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_1_group, $permission_1_group, $user_id));

$User->disable_memory_cache();

var_dump('Set permission');
var_dump($User->set_permission($permission_1_group, $permission_1_group, 1));
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->set_permission($permission_1_group, $permission_1_group, 0));
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->set_permission($permission_2_group, $permission_2_group, 0, $user_id));
var_dump($User->get_permission($permission_2_group, $permission_2_group, $user_id));
var_dump($User->set_permission($permission_2_group, $permission_2_group, 1, $user_id));
var_dump($User->get_permission($permission_2_group, $permission_2_group, $user_id));

var_dump('Delete permission');
$User->set_permission($permission_1_group, $permission_1_group, 0);
var_dump($User->del_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_1_group, $permission_1_group));
$User->set_permission($permission_1_group, $permission_1_group, 0);
var_dump($User->del_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_1_group, $permission_1_group));
$User->set_permission($permission_2_group, $permission_2_group, 0);
var_dump($User->del_permission($permission_2_group, $permission_2_group, $user_id));
var_dump($User->get_permission($permission_2_group, $permission_2_group, $user_id));
$User->set_permission($permission_2_group, $permission_2_group, 0);
var_dump($User->del_permission($permission_2_group, $permission_2_group, $user_id));
var_dump($User->get_permission($permission_2_group, $permission_2_group, $user_id));

var_dump('Get permissions (not set)');
var_dump($User->get_permissions());
var_dump($User->get_permissions($user_id));

var_dump('Set permissions');
var_dump($User->set_permissions([$permission_1 => 1, $permission_2 => 0]));
var_dump($User->get_permissions());
var_dump($User->set_permissions([$permission_1 => 0, $permission_2 => -1]));
var_dump($User->get_permissions());
var_dump($User->set_permissions([$permission_1 => 0, $permission_2 => 1], $user_id));
var_dump($User->get_permissions($user_id));
var_dump($User->set_permissions([$permission_1 => -1, $permission_2 => 0], $user_id));
var_dump($User->get_permissions($user_id));

var_dump('Del permissions all');
var_dump($User->del_permissions_all());
var_dump($User->get_permissions());
$User->set_permissions([$permission_1 => 1, $permission_2 => 0]);
var_dump($User->del_permissions_all($user_id));
var_dump($User->get_permissions());

var_dump('Groups inheritance');
$User->set_groups([$group_1, $group_2]);
$Group->set_permissions([$permission_1 => 1], $group_1);
$Group->set_permissions([$permission_2 => 0], $group_2);
var_dump($User->get_permissions());
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_2_group, $permission_2_group));

var_dump('User overriding');
$User->set_permissions([$permission_2 => 1]);
var_dump($User->get_permissions());
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_2_group, $permission_2_group));

var_dump('Groups overriding');
$Group->set_permissions([$permission_1 => 0], $group_2);
var_dump($User->get_permissions());
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_2_group, $permission_2_group));

var_dump('User overriding #2');
$User->set_permissions([$permission_1 => 1]);
var_dump($User->get_permissions());
var_dump($User->get_permission($permission_1_group, $permission_1_group));
var_dump($User->get_permission($permission_2_group, $permission_2_group));

var_dump('Set permission (root user)');
var_dump($User->set_permission($permission_1_group, $permission_1_group, 0, User::ROOT_ID));

var_dump('Get permission (root user)');
var_dump($User->get_permission($permission_1_group, $permission_1_group, User::ROOT_ID));

var_dump('Del permission (root user)');
var_dump($User->del_permission($permission_1_group, $permission_1_group, User::ROOT_ID));

var_dump('Set permissions (root user)');
var_dump($User->set_permissions([$permission_1 => 0, $permission_2 => 0], User::ROOT_ID));

var_dump('Get permissions (root user)');
var_dump($User->get_permissions(User::ROOT_ID));

var_dump('Del permissions all (root user)');
var_dump($User->del_permissions_all(User::ROOT_ID));

var_dump('Set permission for non-existing permission');
var_dump($User->set_permission('xyz', 'xyz', 0));
?>
--EXPECT--
string(14) "Prepare groups"
bool(true)
bool(true)
string(19) "Prepare permissions"
string(12) "Prepare user"
bool(true)
string(24) "Get permission (not set)"
bool(true)
bool(true)
string(14) "Set permission"
bool(true)
bool(true)
bool(true)
bool(false)
bool(true)
bool(false)
bool(true)
bool(true)
string(17) "Delete permission"
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
bool(true)
string(25) "Get permissions (not set)"
array(0) {
}
array(0) {
}
string(15) "Set permissions"
bool(true)
array(2) {
  [4]=>
  int(1)
  [5]=>
  int(0)
}
bool(true)
array(1) {
  [4]=>
  int(0)
}
bool(true)
array(2) {
  [4]=>
  int(0)
  [5]=>
  int(1)
}
bool(true)
array(1) {
  [5]=>
  int(0)
}
string(19) "Del permissions all"
bool(true)
array(0) {
}
bool(true)
array(0) {
}
string(18) "Groups inheritance"
array(0) {
}
bool(true)
bool(false)
string(15) "User overriding"
array(1) {
  [5]=>
  int(1)
}
bool(true)
bool(true)
string(17) "Groups overriding"
array(1) {
  [5]=>
  int(1)
}
bool(false)
bool(true)
string(18) "User overriding #2"
array(2) {
  [4]=>
  int(1)
  [5]=>
  int(1)
}
bool(true)
bool(true)
string(26) "Set permission (root user)"
bool(false)
string(26) "Get permission (root user)"
bool(true)
string(26) "Del permission (root user)"
bool(false)
string(27) "Set permissions (root user)"
bool(false)
string(27) "Get permissions (root user)"
bool(false)
string(31) "Del permissions all (root user)"
bool(false)
string(42) "Set permission for non-existing permission"
bool(false)
