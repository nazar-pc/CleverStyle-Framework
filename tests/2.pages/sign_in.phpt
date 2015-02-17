--TEST--
Sign in test
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
// Simulate regular initialization
$_SERVER->request_uri    = '/api/System/user/sign_in';
$_SERVER->request_method = 'POST';
Event::instance()->on('System/User/construct/after', function () {
	$_POST['login']    = hash('sha224', 'admin');
	$_POST['password'] = hash('sha512', hash('sha512', 1111).Core::instance()->public_key);
});
Language::instance();
Index::instance();
shutdown_function(true);
shutdown_function();
?>
--EXPECT--
null
