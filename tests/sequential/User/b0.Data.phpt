--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$User = User::instance();
var_dump('Register test user');
$User->registration($User->registration('m1@test.com', false, true));
var_dump($User->id);

var_dump('Set/get data (single)');
var_dump($User->set_data('one', 'One value'));
var_dump($User->get_data('one'));

var_dump('Set/get data (multiple)');
var_dump(
	$User->set_data(
		[
			'two'   => 'Two value',
			'three' => 'Three value'
		]
	)
);
var_dump($User->get_data('two'));
var_dump($User->get_data(['two', 'three']));

var_dump('Del data (single)');
var_dump($User->del_data('one'));
var_dump($User->get_data('one'));

var_dump('Del data (multiple)');
var_dump($User->del_data(['two', 'three']));
var_dump($User->get_data(['two', 'three']));

var_dump('Set/get/del data (user specified explicitly)');
var_dump($User->set_data('exp', 'xyz', User::ROOT_ID));
var_dump($User->get_data('exp', User::ROOT_ID));
var_dump($User->del_data('exp', User::ROOT_ID));
var_dump($User->get_data('exp', User::ROOT_ID));

var_dump('Wrong item key');
var_dump($User->set_data('', 'xyz'));
var_dump($User->get_data(''));
var_dump($User->del_data(''));

var_dump('Set/get/del data (guest user)');
var_dump($User->set_data('exp', 'xyz', User::GUEST_ID));
var_dump($User->get_data('exp', User::GUEST_ID));
var_dump($User->del_data('exp', User::GUEST_ID));
?>
--EXPECT--
string(18) "Register test user"
int(17)
string(21) "Set/get data (single)"
bool(true)
string(9) "One value"
string(23) "Set/get data (multiple)"
bool(true)
string(9) "Two value"
array(2) {
  ["two"]=>
  string(9) "Two value"
  ["three"]=>
  string(11) "Three value"
}
string(17) "Del data (single)"
bool(true)
bool(false)
string(19) "Del data (multiple)"
bool(true)
array(0) {
}
string(44) "Set/get/del data (user specified explicitly)"
bool(true)
string(3) "xyz"
bool(true)
bool(false)
string(14) "Wrong item key"
bool(false)
bool(false)
bool(false)
string(29) "Set/get/del data (guest user)"
bool(false)
bool(false)
bool(false)
