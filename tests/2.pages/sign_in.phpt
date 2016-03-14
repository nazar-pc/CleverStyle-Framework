--TEST--
Sign in test
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Request         = Request::instance();
$Request->path   = '/api/System/user/sign_in';
$Request->uri    = '/api/System/user/sign_in';
$Request->method = 'POST';
Event::instance()->on(
	'System/User/construct/after',
	function () {
		$_POST['login']    = hash('sha224', 'admin');
		$_POST['password'] = hash('sha512', hash('sha512', 1111).Core::instance()->public_key);
	}
);
Language::instance();
Index::instance();
shutdown_function(true);
shutdown_function();
echo Response::instance()->body;
?>
--EXPECT--
null
