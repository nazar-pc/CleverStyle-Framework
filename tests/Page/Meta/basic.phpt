--TEST--
Basic Meta functionality
--FILE--
<?php
namespace cs;
use cs\Page\Meta;
include __DIR__.'/../../custom_loader.php';
include __DIR__.'/../../_SERVER.php';
// Just initialize Language object
Language::instance();
Meta::instance()
	->article()
	->article('section', 'CMS')
	->render();
$Page	= Page::instance();
echo $Page->Head;
?>
--EXPECT--
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<meta content="article" property="og:type">
	<meta content="CMS" property="article:section">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
</head>
