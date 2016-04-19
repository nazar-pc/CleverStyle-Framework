--TEST--
Sign in test
--FILE--
<?php
namespace cs;
include __DIR__.'/../bootstrap.php';
$_SERVER['REQUEST_URI']           = '/api/System/profile';
$_SERVER['REQUEST_METHOD']        = 'SIGN_IN';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
$_POST['login']                   = hash('sha224', 'admin');
$_POST['password']                = hash('sha512', hash('sha512', 1111).Core::instance()->public_key);
Request::instance()->init_from_globals();
App::instance()->execute();
echo Response::instance()->body;
?>
--EXPECT--
null
