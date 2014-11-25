--TEST--
Test Meta functionality with images
--FILE--
<?php
namespace cs;
use cs\Page\Meta;
include __DIR__.'/../../custom_loader.php';
include __DIR__.'/../../_SERVER.php';
// Just initialize Language object
Language::instance();
Meta::instance()
	->image('image.jpg')
	->image([
		'image_set_1.jpg',
		'image_set_2.jpg',
		'image_set_3.jpg'
	])
	->render();
$Page	= Page::instance();
echo $Page->Head;
?>
--EXPECT--
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<meta content="image.jpg" property="og:image">
	<meta content="image_set_1.jpg" property="og:image">
	<meta content="image_set_2.jpg" property="og:image">
	<meta content="image_set_3.jpg" property="og:image">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
	<link href="/image.jpg" rel="image_src">
</head>
