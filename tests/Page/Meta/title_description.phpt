--TEST--
Meta functionality with title and description
--FILE--
<?php
namespace cs;
use cs\Page\Meta;
include __DIR__.'/../../custom_loader.php';
home_page(true);
Config::instance_stub(
	[
		'core'		=> [
			'multilingual'	=> false,
			'name'			=> ''
		]
	],
	[
		'base_url'	=> 'http://cscms.travis',
		'module'	=> False_class::instance()
	]
);
$Page	= Page::instance_stub([
	'canonical_url'	=> false
]);
Text::instance_stub([], [
	'process'	=> 'Web-site'
]);
Meta::instance()
	->og('title', 'Some title')
	->og('description', 'Long description text')
	->render();
echo $Page->Head;
?>
--EXPECT--
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<meta content="Some title" property="og:title">
	<meta content="Long description text" property="og:description">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
</head>
