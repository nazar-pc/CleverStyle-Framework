--TEST--
Sign in test
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$_SERVER['REQUEST_URI']    = '/api/System/user/sign_in';
$_SERVER['REQUEST_METHOD'] = 'POST';
Request::instance()->init_from_globals();
Event::instance()->on(
	'System/User/construct/after',
	function () {
		$_POST['login']    = hash('sha224', 'admin');
		$_POST['password'] = hash('sha512', hash('sha512', 1111).Core::instance()->public_key);
	}
);
Index::instance()->__finish();
Page::instance()->__finish();
User::instance(true)->__finish();
echo Response::instance()->body;
?>
--EXPECT--
null
