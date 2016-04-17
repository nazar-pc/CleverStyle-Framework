--TEST--
Administration page rendering (403)
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$_SERVER['REQUEST_URI'] = '/admin';
do_request();
echo Response::instance()->body;
?>
--EXPECT--
<!doctype html>
<title>403 Forbidden</title>
403 Forbidden
