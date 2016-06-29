--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Config::instance()->core['site_mode'] = 0;
do_request();
echo Response::instance()->body;
?>
--EXPECTF--
<!doctype html>
<title>Site closed</title>
<p>Site closed for maintenance</p>
