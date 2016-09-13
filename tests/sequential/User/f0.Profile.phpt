--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Request::instance()->init_from_globals();
$Event   = Event::instance();
$Session = Session::instance();
$User    = User::instance();
var_dump('Prepare user');
/** @noinspection OffsetOperationsInspection */
$user_id_1 = $User->registration('fi1@test.com', false, true)['id'];
/** @noinspection OffsetOperationsInspection */
$user_id_2 = $User->registration('fi2@test.com', false, false)['id'];

var_dump('Get username');
var_dump($User->get('username'));
var_dump($User->get('username', User::ROOT_ID));
var_dump($User->username());
var_dump($User->username(User::ROOT_ID));
var_dump($User->username);

var_dump('Set username');
var_dump($User->set('username', 'Username 1'));
var_dump($User->username);
var_dump($User->set('username', 'Username 1', User::ROOT_ID));
var_dump($User->username(User::ROOT_ID));
$User->set('username', '', User::ROOT_ID);
$User->username = 'Username 2';
var_dump($User->username);

var_dump('Avatar');
var_dump($User->get('avatar'));
var_dump($User->avatar);
var_dump($User->avatar());
var_dump($User->avatar(256));

Config::instance()->core['gravatar_support'] = 1;
var_dump('Avatar (gravatar)');
var_dump($User->avatar());
var_dump($User->avatar(256));

$Event
	->on(
		'System/upload_files/del_tag',
		function ($data) {
			var_dump('System/upload_files/del_tag event fired with', $data);
		}
	)
	->on(
		'System/upload_files/add_tag',
		function ($data) {
			var_dump('System/upload_files/add_tag event fired with', $data);
		}
	);

var_dump('Set avatar');
var_dump($User->set('avatar', 'http://xyz.xyz/xyz'));
var_dump($User->avatar);
var_dump($User->avatar(null, User::ROOT_ID));

var_dump('Set incorrect avatar (not absolute URL with protocol)');
var_dump($User->set('avatar', 'foo'));
var_dump($User->avatar);

var_dump('Set language');
var_dump($User->set('language', 'English'));
var_dump($User->language);

var_dump('Set incorrect language');
var_dump($User->set('language', 'Foo'));
var_dump($User->language);

var_dump('Set timezone');
var_dump($User->set('timezone', 'UTC'));
var_dump($User->timezone);

var_dump('Set incorrect timezone');
var_dump($User->set('timezone', 'Foo'));
var_dump($User->timezone);

var_dump('Set login');
var_dump($User->set('login', 'zyx'));
var_dump($User->login);
var_dump($User->login_hash === hash('sha224', 'zyx'));

var_dump('Set login to own email');
var_dump($User->set('login', $User->email));
var_dump($User->login);

var_dump("Set login to someone else's email");
var_dump($User->set('login', 'test@example.com'));
var_dump($User->login);

var_dump('Set to the same login');
var_dump($User->set('login', 'zyx'));
var_dump($User->set('login', $User->get('login', $user_id_2)));

var_dump('Set email');
var_dump($User->set('email', 'zyx@test.com'));
var_dump($User->email);
var_dump($User->email_hash === hash('sha224', 'zyx@test.com'));

var_dump('Set to the same email');
var_dump($User->set('email', 'zyx@test.com'));
var_dump($User->set('email', $User->get('email', $user_id_2)));

var_dump('Set to incorrect email');
var_dump($User->set('email', 'xyz'));

$Event->on(
	'System/Session/del_all',
	function ($data) {
		var_dump('System/Session/del_all event fired with', $data);
	}
);

var_dump('Set password hash');
var_dump($User->set('password_hash', 'bad hash'));
var_dump($User->id);

$Session->add($user_id_1);
var_dump('Set status');
var_dump($User->set('status', User::STATUS_INACTIVE));
var_dump($User->id);

$Session->add($user_id_1);
var_dump('Set multiple');
var_dump(
	$User->set(
		[
			'login' => 'abc',
			'email' => 'abc@test.com'
		]
	)
);

var_dump('Get multiple');
var_dump($User->get(['login', 'email']));

var_dump('Set for guest');
var_dump($User->set('email', 'xyz@test.com', User::GUEST_ID));

var_dump('Set id');
var_dump($User->set('id', 999, $user_id_2));

var_dump('Set non-existing column');
var_dump($User->set('whatever', 'foo', $user_id_2));

var_dump('Get id by login or email hash');
var_dump($User->get_id(hash('sha224', 'fi2@test.com')));
var_dump($User->get_id(hash('sha224', 'fi2')));

var_dump('Get id by incorrect login or email hash');
var_dump($User->get_id('foo'));

var_dump('Columns in users table');
var_dump($User->get_users_columns());
?>
--EXPECT--
string(12) "Prepare user"
string(12) "Get username"
string(0) ""
string(0) ""
string(3) "fi1"
string(5) "admin"
string(0) ""
string(12) "Set username"
bool(true)
string(10) "Username 1"
bool(true)
string(10) "Username 1"
string(10) "Username 2"
string(6) "Avatar"
string(0) ""
string(0) ""
string(42) "http://cscms.travis/includes/img/guest.svg"
string(42) "http://cscms.travis/includes/img/guest.svg"
string(17) "Avatar (gravatar)"
string(129) "https://www.gravatar.com/avatar/89beedc929f0adf64bf48c0f9131c4f0?d=mm&s=&d=http%3A%2F%2Fcscms.travis%2Fincludes%2Fimg%2Fguest.svg"
string(132) "https://www.gravatar.com/avatar/89beedc929f0adf64bf48c0f9131c4f0?d=mm&s=256&d=http%3A%2F%2Fcscms.travis%2Fincludes%2Fimg%2Fguest.svg"
string(10) "Set avatar"
string(44) "System/upload_files/del_tag event fired with"
array(2) {
  ["url"]=>
  string(0) ""
  ["tag"]=>
  string(15) "users/20/avatar"
}
string(44) "System/upload_files/add_tag event fired with"
array(2) {
  ["url"]=>
  string(18) "http://xyz.xyz/xyz"
  ["tag"]=>
  string(15) "users/20/avatar"
}
bool(true)
string(18) "http://xyz.xyz/xyz"
string(129) "https://www.gravatar.com/avatar/0f7026e1d8521d8cf179aab4533b85f2?d=mm&s=&d=http%3A%2F%2Fcscms.travis%2Fincludes%2Fimg%2Fguest.svg"
string(53) "Set incorrect avatar (not absolute URL with protocol)"
string(44) "System/upload_files/del_tag event fired with"
array(2) {
  ["url"]=>
  string(18) "http://xyz.xyz/xyz"
  ["tag"]=>
  string(15) "users/20/avatar"
}
string(44) "System/upload_files/add_tag event fired with"
array(2) {
  ["url"]=>
  string(0) ""
  ["tag"]=>
  string(15) "users/20/avatar"
}
bool(true)
string(0) ""
string(12) "Set language"
bool(true)
string(7) "English"
string(22) "Set incorrect language"
bool(true)
string(0) ""
string(12) "Set timezone"
bool(true)
string(3) "UTC"
string(22) "Set incorrect timezone"
bool(true)
string(0) ""
string(9) "Set login"
bool(true)
string(3) "zyx"
bool(true)
string(22) "Set login to own email"
bool(true)
string(12) "fi1@test.com"
string(33) "Set login to someone else's email"
bool(false)
string(12) "fi1@test.com"
string(21) "Set to the same login"
bool(true)
bool(false)
string(9) "Set email"
bool(true)
string(12) "zyx@test.com"
bool(true)
string(21) "Set to the same email"
bool(true)
bool(false)
string(22) "Set to incorrect email"
bool(false)
string(17) "Set password hash"
string(39) "System/Session/del_all event fired with"
array(1) {
  ["id"]=>
  int(20)
}
bool(true)
int(1)
string(10) "Set status"
string(39) "System/Session/del_all event fired with"
array(1) {
  ["id"]=>
  int(20)
}
bool(true)
int(1)
string(12) "Set multiple"
bool(false)
string(12) "Get multiple"
array(2) {
  ["login"]=>
  string(5) "guest"
  ["email"]=>
  string(0) ""
}
string(13) "Set for guest"
bool(false)
string(6) "Set id"
bool(false)
string(23) "Set non-existing column"
bool(false)
string(29) "Get id by login or email hash"
int(21)
int(21)
string(39) "Get id by incorrect login or email hash"
bool(false)
string(22) "Columns in users table"
array(15) {
  [0]=>
  string(2) "id"
  [1]=>
  string(5) "login"
  [2]=>
  string(10) "login_hash"
  [3]=>
  string(8) "username"
  [4]=>
  string(13) "password_hash"
  [5]=>
  string(5) "email"
  [6]=>
  string(10) "email_hash"
  [7]=>
  string(8) "language"
  [8]=>
  string(8) "timezone"
  [9]=>
  string(8) "reg_date"
  [10]=>
  string(6) "reg_ip"
  [11]=>
  string(7) "reg_key"
  [12]=>
  string(6) "status"
  [13]=>
  string(11) "block_until"
  [14]=>
  string(6) "avatar"
}
