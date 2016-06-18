--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$_SERVER['REQUEST_URI'] = '/admin';
do_request();
echo Response::instance()->body;
?>
--EXPECT--
<!doctype html>
<title>403 Forbidden</title>
403 Forbidden
