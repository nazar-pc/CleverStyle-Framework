--FILE--
<?php
namespace cs;
use cs\Page\Meta;
include __DIR__.'/../../../bootstrap.php';
Request::instance()->init_from_globals();
Meta::instance()
	->og('', 'Something without key')
	->render();
echo Page::instance()->Head;
?>
--EXPECT--
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<meta content="Web-site" property="og:title">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
</head>
