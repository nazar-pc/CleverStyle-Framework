--FILE--
<?php
namespace PHPMailer\PHPMailer {
	class PHPMailer {
		public function __set ($name, $value) {
			var_dump("PHPMailer->$name set to", $value);
		}
		public function __call ($name, $arguments) {
			var_dump("PHPMailer->$name() called with", $arguments);
		}
		public function addAttachment (...$arguments) {
			if (!file_exists($arguments[0])) {
				throw new Exception("Attachment file doesn't exists");
			}
			var_dump("PHPMailer->addAttachment() called with", $arguments);
		}
		public function send ($result = null, $exception = null) {
			static $cache = [
				'exception' => false,
				'return'    => true
			];
			var_dump("PHPMailer->send() called");
			if ($result !== null) {
				$cache['result'] = $result;
			}
			if ($exception !== null) {
				$cache['exception'] = $exception;
				return true;
			}
			if ($cache['exception']) {
				throw new Exception('Sending failed');
			}
			return $cache['return'];
		}
	}

	class Exception extends \Exception {
	}
}
namespace cs {
	include __DIR__.'/../../unit.php';

	class Test_mail extends Mail {
		public static function test () {
			$Config = Config::instance_stub(
				[
					'core' => [
						'smtp'           => 0,
						'smtp_host'      => 'smtp.test.com',
						'smtp_port'      => 587,
						'smtp_secure'    => 'tls',
						'smtp_auth'      => 0,
						'smtp_user'      => 'admin@test.com',
						'smtp_password'  => 'smtp password',
						'mail_from'      => 'admin@test.com',
						'mail_from_name' => 'mail_from_name',
						'mail_signature' => 'mail_signature'
					]
				]
			);
			$Mail   = self::instance();

			var_dump('Basic mail sending (no smtp)');
			var_dump($Mail->send_to('user@test.com', 'Test email', '<strong>HTML body content</strong>'));

			var_dump('Basic mail sending (smtp, no auth)');
			$Config->core['smtp'] = 1;
			var_dump($Mail->send_to('user@test.com', 'Test email', '<strong>HTML body content</strong>'));

			var_dump('Basic mail sending (smtp, auth)');
			$Config->core['smtp_auth'] = 1;
			var_dump($Mail->send_to('user@test.com', 'Test email', '<strong>HTML body content</strong>'));

			var_dump('Empty email, subject or body');
			var_dump($Mail->send_to('', 'Test email', '<strong>HTML body content</strong>'));
			var_dump($Mail->send_to('user@test.com', '', '<strong>HTML body content</strong>'));
			var_dump($Mail->send_to('user@test.com', 'Test email', ''));

			var_dump('Expanded email format, text body, attachment, reply to');
			$Config->core['smtp'] = 0;
			var_dump(
				$Mail->send_to(
					[
						['user@test.com'],
						['user2@test.com', 'Username2']
					],
					'Test email',
					'<strong>HTML body content</strong>',
					'Text body content',
					__DIR__.'/attachment.txt',
					'replyto@test.com'
				)
			);

			var_dump('Email normalization');
			var_dump($Mail->normalize_email('user@test.com'));
			var_dump($Mail->normalize_email('user@test.com, user2@test.com'));
			var_dump($Mail->normalize_email(['user@test.com', 'Username']));
			var_dump(
				$Mail->normalize_email(
					[
						['user@test.com', 'Username'],
						['user2@test.com', 'Username2']
					]
				)
			);
			var_dump(
				$Mail->normalize_email(
					[
						['user@test.com'],
						['user2@test.com', 'Username2']
					]
				)
			);

			var_dump('Making signature');
			var_dump($Mail->make_signature($Config, true));
			var_dump($Mail->make_signature($Config, false));
			var_dump($Mail->make_signature($Config, 'Custom signature'));

			var_dump('Body normalization');
			var_dump($Mail->normalize_body('Just html', ''));
			var_dump($Mail->normalize_body('<html>With html tag</html>', ''));
			var_dump($Mail->normalize_body('<body>With body tag</body>', ''));

			var_dump('Normalize attachment');
			var_dump($Mail->normalize_attachment(__DIR__.'/attachment.txt'));
			var_dump($Mail->normalize_attachment([__DIR__.'/attachment.txt']));
			var_dump($Mail->normalize_attachment([__DIR__.'/attachment.txt', 'File.txt']));
			var_dump(
				$Mail->normalize_attachment(
					[
						[__DIR__.'/attachment.txt'],
						[__DIR__.'/attachment2.txt', 'File2.txt'],
					]
				)
			);

			var_dump('Attachment addition fails with exception, sending fails with exception');
			$PHPMailer = new \PHPMailer\PHPMailer\PHPMailer;
			$PHPMailer->send(null, true);
			var_dump($Mail->send_to('user@test.com', 'Test email', '<strong>HTML body content</strong>', null, __DIR__.'/attachment2.txt'));
		}
	}
	Test_mail::test();
}
?>
--EXPECTF--
string(28) "Basic mail sending (no smtp)"
string(22) "PHPMailer->From set to"
string(14) "admin@test.com"
string(26) "PHPMailer->FromName set to"
string(14) "mail_from_name"
string(25) "PHPMailer->CharSet set to"
string(5) "utf-8"
string(31) "PHPMailer->isHTML() called with"
array(0) {
}
string(35) "PHPMailer->addAddress() called with"
array(1) {
  [0]=>
  string(13) "user@test.com"
}
string(25) "PHPMailer->Subject set to"
string(10) "Test email"
string(22) "PHPMailer->Body set to"
string(210) "<!doctype html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>
		<strong>HTML body content</strong><br>
		<br>
		--%w
		<br>mail_signature
	</body>
</html>
"
string(24) "PHPMailer->send() called"
bool(true)
string(34) "Basic mail sending (smtp, no auth)"
string(31) "PHPMailer->isSMTP() called with"
array(0) {
}
string(22) "PHPMailer->Host set to"
string(13) "smtp.test.com"
string(22) "PHPMailer->Port set to"
int(587)
string(28) "PHPMailer->SMTPSecure set to"
string(3) "tls"
string(22) "PHPMailer->From set to"
string(14) "admin@test.com"
string(26) "PHPMailer->FromName set to"
string(14) "mail_from_name"
string(25) "PHPMailer->CharSet set to"
string(5) "utf-8"
string(31) "PHPMailer->isHTML() called with"
array(0) {
}
string(35) "PHPMailer->addAddress() called with"
array(1) {
  [0]=>
  string(13) "user@test.com"
}
string(25) "PHPMailer->Subject set to"
string(10) "Test email"
string(22) "PHPMailer->Body set to"
string(210) "<!doctype html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>
		<strong>HTML body content</strong><br>
		<br>
		--%w
		<br>mail_signature
	</body>
</html>
"
string(24) "PHPMailer->send() called"
bool(true)
string(31) "Basic mail sending (smtp, auth)"
string(31) "PHPMailer->isSMTP() called with"
array(0) {
}
string(22) "PHPMailer->Host set to"
string(13) "smtp.test.com"
string(22) "PHPMailer->Port set to"
int(587)
string(28) "PHPMailer->SMTPSecure set to"
string(3) "tls"
string(26) "PHPMailer->SMTPAuth set to"
bool(true)
string(26) "PHPMailer->Username set to"
string(14) "admin@test.com"
string(26) "PHPMailer->Password set to"
string(13) "smtp password"
string(22) "PHPMailer->From set to"
string(14) "admin@test.com"
string(26) "PHPMailer->FromName set to"
string(14) "mail_from_name"
string(25) "PHPMailer->CharSet set to"
string(5) "utf-8"
string(31) "PHPMailer->isHTML() called with"
array(0) {
}
string(35) "PHPMailer->addAddress() called with"
array(1) {
  [0]=>
  string(13) "user@test.com"
}
string(25) "PHPMailer->Subject set to"
string(10) "Test email"
string(22) "PHPMailer->Body set to"
string(210) "<!doctype html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>
		<strong>HTML body content</strong><br>
		<br>
		--%w
		<br>mail_signature
	</body>
</html>
"
string(24) "PHPMailer->send() called"
bool(true)
string(28) "Empty email, subject or body"
bool(false)
bool(false)
bool(false)
string(54) "Expanded email format, text body, attachment, reply to"
string(22) "PHPMailer->From set to"
string(14) "admin@test.com"
string(26) "PHPMailer->FromName set to"
string(14) "mail_from_name"
string(25) "PHPMailer->CharSet set to"
string(5) "utf-8"
string(31) "PHPMailer->isHTML() called with"
array(0) {
}
string(35) "PHPMailer->addAddress() called with"
array(1) {
  [0]=>
  string(13) "user@test.com"
}
string(35) "PHPMailer->addAddress() called with"
array(2) {
  [0]=>
  string(14) "user2@test.com"
  [1]=>
  string(9) "Username2"
}
string(35) "PHPMailer->addReplyTo() called with"
array(1) {
  [0]=>
  string(16) "replyto@test.com"
}
string(38) "PHPMailer->addAttachment() called with"
array(1) {
  [0]=>
  string(%d) "%s/tests/quick/Mail/attachment.txt"
}
string(25) "PHPMailer->Subject set to"
string(10) "Test email"
string(22) "PHPMailer->Body set to"
string(210) "<!doctype html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>
		<strong>HTML body content</strong><br>
		<br>
		--%w
		<br>mail_signature
	</body>
</html>
"
string(25) "PHPMailer->AltBody set to"
string(37) "Text body content

--%w
mail_signature"
string(24) "PHPMailer->send() called"
bool(true)
string(19) "Email normalization"
array(1) {
  [0]=>
  array(1) {
    [0]=>
    string(13) "user@test.com"
  }
}
array(2) {
  [0]=>
  array(1) {
    [0]=>
    string(13) "user@test.com"
  }
  [1]=>
  array(1) {
    [0]=>
    string(14) "user2@test.com"
  }
}
array(1) {
  [0]=>
  array(2) {
    [0]=>
    string(13) "user@test.com"
    [1]=>
    string(8) "Username"
  }
}
array(2) {
  [0]=>
  array(2) {
    [0]=>
    string(13) "user@test.com"
    [1]=>
    string(8) "Username"
  }
  [1]=>
  array(2) {
    [0]=>
    string(14) "user2@test.com"
    [1]=>
    string(9) "Username2"
  }
}
array(2) {
  [0]=>
  array(1) {
    [0]=>
    string(13) "user@test.com"
  }
  [1]=>
  array(2) {
    [0]=>
    string(14) "user2@test.com"
    [1]=>
    string(9) "Username2"
  }
}
string(16) "Making signature"
string(32) "<br>
<br>
--%w
<br>mail_signature"
string(0) ""
string(34) "<br>
<br>
--%w
<br>Custom signature"
string(18) "Body normalization"
string(142) "<!doctype html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>Just html</body>
</html>
"
string(42) "<!doctype html>
<html>With html tag</html>"
string(146) "<!doctype html>
<html>
	<head>
		<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>With body tag</body>
</html>
"
string(20) "Normalize attachment"
array(1) {
  [0]=>
  array(1) {
    [0]=>
    string(%d) "%s/tests/quick/Mail/attachment.txt"
  }
}
array(1) {
  [0]=>
  array(1) {
    [0]=>
    string(%d) "%s/tests/quick/Mail/attachment.txt"
  }
}
array(1) {
  [0]=>
  array(2) {
    [0]=>
    string(%d) "%s/tests/quick/Mail/attachment.txt"
    [1]=>
    string(8) "File.txt"
  }
}
array(2) {
  [0]=>
  array(1) {
    [0]=>
    string(%d) "%s/tests/quick/Mail/attachment.txt"
  }
  [1]=>
  array(2) {
    [0]=>
    string(%d) "%s/tests/quick/Mail/attachment2.txt"
    [1]=>
    string(9) "File2.txt"
  }
}
string(70) "Attachment addition fails with exception, sending fails with exception"
string(24) "PHPMailer->send() called"
string(22) "PHPMailer->From set to"
string(14) "admin@test.com"
string(26) "PHPMailer->FromName set to"
string(14) "mail_from_name"
string(25) "PHPMailer->CharSet set to"
string(5) "utf-8"
string(31) "PHPMailer->isHTML() called with"
array(0) {
}
string(35) "PHPMailer->addAddress() called with"
array(1) {
  [0]=>
  string(13) "user@test.com"
}
%A
Warning: Attachment file doesn't exists in %s/core/classes/Mail.php on line %d
bool(false)
