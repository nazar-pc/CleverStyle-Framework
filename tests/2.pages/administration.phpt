--TEST--
Home page rendering
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
$Request       = Request::instance();
$Request->path = '/admin';
$Request->uri  = '/admin';
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
echo Response::instance()->body;
?>
--EXPECT--
<!doctype html>
<title>403 Forbidden</title>
403 Forbidden
