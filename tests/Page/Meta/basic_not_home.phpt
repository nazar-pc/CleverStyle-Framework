--TEST--
Basic Meta functionality
--FILE--
<?php
namespace cs;
use cs\Page\Meta;
include __DIR__.'/../../custom_loader.php';
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
Request::instance_stub(
	[
		'path_normalized' => 'System'
	]
);
$Page	= Page::instance_stub([
	'canonical_url'	=> false
]);
Text::instance_stub([], [
	'process'	=> 'Web-site'
]);
Meta::instance()
	->article()
	->article('section', 'CMS')
	->render();
echo $Page->Head;
?>
--EXPECT--
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<meta content="article" property="og:type">
	<meta content="CMS" property="article:section">
	<meta content="http://cscms.travis/System" property="og:url">
	<meta content="Web-site" property="og:site_name">
</head>
