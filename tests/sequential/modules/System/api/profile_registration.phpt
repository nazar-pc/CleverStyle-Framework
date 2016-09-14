--FILE--
<?php
namespace cs;
include __DIR__.'/../../../../bootstrap.php';
$Config = Config::instance();
$User   = User::instance();
/** @noinspection OffsetOperationsInspection */
$user_id    = $User->registration('cp1@test.com', false, false)['id'];
$public_key = Core::instance()->public_key;

Mail::instance_stub(
	[],
	[
		'send_to' => function (...$arguments) {
			var_dump('cs\Mail::send_to() called with', $arguments);
			return true;
		}
	]
);

var_dump('Registration (already registered)');
do_api_request(
	'registration',
	'api/System/profile',
	[],
	[],
	['session' => Session::instance()->add($user_id)]
);

var_dump('Registration (registration not allowed)');
$Config->core['allow_user_registration'] = 0;
do_api_request(
	'registration',
	'api/System/profile'
);
$Config->core['allow_user_registration'] = 1;

var_dump('Registration (no email)');
do_api_request(
	'registration',
	'api/System/profile'
);

var_dump('Registration (incorrect email)');
do_api_request(
	'registration',
	'api/System/profile',
	[
		'email' => 'not an email at all'
	]
);

var_dump('Registration (existing user)');
do_api_request(
	'registration',
	'api/System/profile',
	[
		'email' => $User->get('email', $user_id)
	]
);

Event::instance_stub(
	[],
	[
		'fire' => function (...$arguments) {
			if (strpos($arguments[0], 'System/User') === 0) {
				var_dump('cs\Event::fire() called with', $arguments);
			}
			return true;
		}
	]
);

var_dump('Registration (no confirmation needed, auto sign in)');
$Config->core['require_registration_confirmation'] = 0;
$Config->core['auto_sign_in_after_registration']   = 1;
do_api_request(
	'registration',
	'api/System/profile',
	[
		'email' => 'cp2@test.com'
	]
);

var_dump('Registration (no confirmation needed, no auto sign in)');
$Config->core['require_registration_confirmation'] = 0;
$Config->core['auto_sign_in_after_registration']   = 0;
do_api_request(
	'registration',
	'api/System/profile',
	[
		'email' => 'cp3@test.com'
	]
);

var_dump('Registration (confirmation needed, fill username, password, language, timezone and avatar upfront)');
$Config->core['require_registration_confirmation'] = 1;
do_api_request(
	'registration',
	'api/System/profile',
	[
		'email'    => 'cp4@test.com',
		'username' => 'CP4 user',
		'password' => hash('sha512', hash('sha512', '123456').$public_key),
		'language' => 'English',
		'timezone' => 'UTC',
		'avatar'   => 'http://example.com/avatar.jpg'
	]
);
var_dump($User->get(['username', 'language', 'timezone', 'avatar'], $user_id + 3));
var_dump($User->validate_password('123456', $user_id + 3));

var_dump('Registration (mail sending failed)');
Mail::instance_stub(
	[],
	[
		'send_to' => function () {
			return false;
		}
	]
);
do_api_request(
	'registration',
	'api/System/profile',
	[
		'email' => 'cp5@test.com'
	]
);
var_dump($User->get_id(hash('sha224', 'cp5@test.com')));

Event::instance_stub(
	[],
	[
		'fire' => function (...$arguments) {
			return true;
		}
	]
);

$User->del_user($user_id);
$User->del_user($user_id + 1);
$User->del_user($user_id + 2);
$User->del_user($user_id + 3);
?>
--EXPECTF--
string(33) "Registration (already registered)"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(49) "{"error":403,"error_description":"403 Forbidden"}"
string(39) "Registration (registration not allowed)"
int(403)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(62) "{"error":403,"error_description":"Registration is prohibited"}"
string(23) "Registration (no email)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(62) "{"error":400,"error_description":"Type correct email, please"}"
string(30) "Registration (incorrect email)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(62) "{"error":400,"error_description":"Type correct email, please"}"
string(28) "Registration (existing user)"
int(400)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(193) "{"error":400,"error_description":"Registration error: user with such email address already exists, try to remember, maybe you are registered (if you forgot your password - it can be restored)"}"
string(51) "Registration (no confirmation needed, auto sign in)"
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(31) "System/User/registration/before"
  [1]=>
  array(1) {
    ["email"]=>
    string(12) "cp2@test.com"
  }
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(30) "System/User/registration/after"
  [1]=>
  array(1) {
    ["id"]=>
    int(%d)
  }
}
string(30) "cs\Mail::send_to() called with"
array(3) {
  [0]=>
  string(12) "cp2@test.com"
  [1]=>
  string(47) "Registration of Web-site successfully completed"
  [2]=>
  string(561) "<h3>Hello, cp2!</h3><p>Congratulations, the registration on the site Web-site is successful, and now you have our full participant. For security reasons the password has been generated automatically, you can always change it to another in <a href="http://cscms.travis/profile/settings">your profile settings on site</a>.</p><p>Your login information to the site:</p><table border="0"><tr><td>Login:</td><td>cp2</td></tr><tr><td>Password:</td><td>%s</td></tr></table><p>Do not reply to this letter, it was sent automatically and does not require an answer.</p>"
}
int(201)
array(2) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(118) "session=%s; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(4) "null"
string(54) "Registration (no confirmation needed, no auto sign in)"
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(31) "System/User/registration/before"
  [1]=>
  array(1) {
    ["email"]=>
    string(12) "cp3@test.com"
  }
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(30) "System/User/registration/after"
  [1]=>
  array(1) {
    ["id"]=>
    int(%d)
  }
}
string(30) "cs\Mail::send_to() called with"
array(3) {
  [0]=>
  string(12) "cp3@test.com"
  [1]=>
  string(47) "Registration of Web-site successfully completed"
  [2]=>
  string(561) "<h3>Hello, cp3!</h3><p>Congratulations, the registration on the site Web-site is successful, and now you have our full participant. For security reasons the password has been generated automatically, you can always change it to another in <a href="http://cscms.travis/profile/settings">your profile settings on site</a>.</p><p>Your login information to the site:</p><table border="0"><tr><td>Login:</td><td>cp3</td></tr><tr><td>Password:</td><td>%s</td></tr></table><p>Do not reply to this letter, it was sent automatically and does not require an answer.</p>"
}
int(201)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
string(98) "Registration (confirmation needed, fill username, password, language, timezone and avatar upfront)"
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(31) "System/User/registration/before"
  [1]=>
  array(1) {
    ["email"]=>
    string(12) "cp4@test.com"
  }
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(30) "System/User/registration/after"
  [1]=>
  array(1) {
    ["id"]=>
    int(%d)
  }
}
string(30) "cs\Mail::send_to() called with"
array(3) {
  [0]=>
  string(12) "cp4@test.com"
  [1]=>
  string(43) "Registration on Web-site needs confirmation"
  [2]=>
  string(516) "<h3>Hello, CP4 user!</h3><p>This email was used to register on the site Web-site.</p><p>If it was you - follow the link <a href="http://cscms.travis/profile/registration_confirmation/%s">http://cscms.travis/profile/registration_confirmation/%s</a>, otherwise ignore the letter, and after 1 days unconfirmed account on the server will be automatically deleted.</p><p>Do not reply to this letter, it was sent automatically and does not require an answer.</p>"
}
int(202)
array(1) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
}
string(4) "null"
array(4) {
  ["username"]=>
  string(8) "CP4 user"
  ["language"]=>
  string(7) "English"
  ["timezone"]=>
  string(3) "UTC"
  ["avatar"]=>
  string(29) "http://example.com/avatar.jpg"
}
bool(true)
string(34) "Registration (mail sending failed)"
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(31) "System/User/registration/before"
  [1]=>
  array(1) {
    ["email"]=>
    string(12) "cp5@test.com"
  }
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(30) "System/User/registration/after"
  [1]=>
  array(1) {
    ["id"]=>
    int(%d)
  }
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(22) "System/User/del/before"
  [1]=>
  array(1) {
    ["id"]=>
    int(%d)
  }
}
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(21) "System/User/del/after"
  [1]=>
  array(1) {
    ["id"]=>
    int(%d)
  }
}
int(500)
array(2) {
  ["content-type"]=>
  array(1) {
    [0]=>
    string(31) "application/json; charset=utf-8"
  }
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(118) "session=%s; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(172) "{"error":500,"error_description":"An error occurred during email sending to the provided email. Check if the email address is correct and try again. Registration aborted."}"
bool(false)
