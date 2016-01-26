--TEST--
Home page rendering
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
$_SERVER->request_uri = '/admin';
// Simulate regular initialization
try {
	try {
		Language::instance();
		Index::instance();
		shutdown_function(true);
		shutdown_function();
	} catch (ExitException $e) {
		if ($e->getCode() >= 400) {
			Page::instance()->error($e->getMessage() ?: null, $e->getJson(), $e->getCode());
		}
	}
} catch (ExitException $e) {
}
?>
--EXPECT--
<!doctype html>
<title>403 Forbidden</title>
403 Forbidden
