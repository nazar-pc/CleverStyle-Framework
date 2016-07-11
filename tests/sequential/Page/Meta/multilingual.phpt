--FILE--
<?php
namespace cs;
use cs\Page\Meta;
include __DIR__.'/../../../bootstrap.php';
Request::instance()->init_from_globals();
$Config                           = Config::instance();
$Config->core['multilingual']     = 1;
$Config->core['active_languages'] = [
	'English',
	'Russian',
	'Ukrainian'
];
Meta::instance()->render();
echo Page::instance()->Head;
?>
--EXPECT--
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<meta content="Web-site" property="og:title">
	<meta content="http://cscms.travis/en" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
	<meta content="en_US" property="og:locale">
	<meta content="ru_RU" property="og:locale:alternate">
	<meta content="uk_UA" property="og:locale:alternate">
</head>
