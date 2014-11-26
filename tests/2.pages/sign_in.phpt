--TEST--
Sign in test
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
// Simulate regular initialization
$_SERVER['REQUEST_URI']		= '/api/System/user/sign_in';
$_SERVER['REQUEST_METHOD']	= 'POST';
Trigger::instance()->register('System/User/construct/after', function () {
	$_POST['login']		= hash('sha224', 'admin');
	$_POST['password']	= hash('sha512', hash('sha512', 1111).Core::instance()->public_key);
});
Language::instance();
Index::instance();
shutdown_function(false);
?>
--EXPECT--
null
