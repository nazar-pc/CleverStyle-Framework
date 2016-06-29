--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$_SERVER['REQUEST_URI']               = '/api/System/profile';
$_SERVER['REQUEST_METHOD']            = 'SIGN_IN';
$_SERVER['HTTP_X_REQUESTED_WITH']     = 'XMLHttpRequest';
$_POST['login']                       = hash('sha224', 'admin');
$_POST['password']                    = hash('sha512', hash('sha512', 1111).Core::instance()->public_key);
Config::instance()->core['site_mode'] = 0;
Request::instance()->init_from_globals();
App::instance()->execute();
$Response = Response::instance();
var_dump($Response->headers, $Response->body);
?>
--EXPECTF--
array(2) {
  ["content-language"]=>
  array(1) {
    [0]=>
    string(2) "en"
  }
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(118) "session=%s; path=/; expires=%s, %d-%s-%d %d:%d:%d GMT; domain=cscms.travis; HttpOnly"
  }
}
string(4) "null"

