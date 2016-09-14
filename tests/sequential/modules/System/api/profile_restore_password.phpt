--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$User = User::instance();
/** @noinspection OffsetOperationsInspection */
$user_id = $User->registration('rp@test.com', false, false)['id'];

Mail::instance_stub(
	[],
	[
		'send_to' => function (...$arguments) {
			var_dump('cs\Mail::send_to() called with', $arguments);
			return true;
		}
	]
);

var_dump('Restore password (user already signed in)');
do_api_request(
	'restore_password',
	'api/System/profile',
	[],
	[],
	['session' => Session::instance()->add($user_id)]
);

var_dump('Restore password (no email)');
do_api_request(
	'restore_password',
	'api/System/profile'
);

var_dump('Restore password (user not found)');
do_api_request(
	'restore_password',
	'api/System/profile',
	[
		'email' => 'test@example.com'
	]
);

var_dump('Restore password');
do_api_request(
	'restore_password',
	'api/System/profile',
	[
		'email' => $User->get('email_hash', $user_id)
	]
);

var_dump('Restore password (mail sending failed)');
Mail::instance_stub(
	[],
	[
		'send_to' => function () {
			return false;
		}
	]
);
do_api_request(
	'restore_password',
	'api/System/profile',
	[
		'email' => $User->get('email_hash', $user_id)
	]
);

$User->del_user($user_id);
?>
--EXPECTF--
string(41) "Restore password (user already signed in)"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(49) "{"error":403,"error_description":"403 Forbidden"}"
string(27) "Restore password (no email)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(59) "{"error":400,"error_description":"Please, type your email"}"
string(33) "Restore password (user not found)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(78) "{"error":400,"error_description":"User with such login or email is not found"}"
string(16) "Restore password"
string(30) "cs\Mail::send_to() called with"
array(3) {
  [0]=>
  string(11) "rp@test.com"
  [1]=>
  string(42) "Request for password restoring on Web-site"
  [2]=>
  string(520) "<h3>Hello, rp!</h3><p>You left password recovery request on website Web-site.</p><p>If it was you - follow the link <a href="http://cscms.travis/profile/restore_password_confirmation/%s">http://cscms.travis/profile/restore_password_confirmation/%s</a>, otherwise ignore the letter, and after 1 days unconfirmed account on the server will be automatically deleted.</p><p>Do not reply to this letter, it was sent automatically and does not require an answer.</p>"
}
int(200)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(38) "Restore password (mail sending failed)"
int(500)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(159) "{"error":500,"error_description":"Password restoring error: fault on the server. Wait for several minutes, please, try to restore password from th beginning."}"
