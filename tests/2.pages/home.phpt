--TEST--
Home page rendering
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
do_request();
echo Response::instance()->body;
?>
--EXPECTF--
<!doctype html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<title>Web-site | Home</title>
	<meta charset="utf-8">
	<meta content="CleverStyle CMS by Mokrynskyi Nazar" name="generator">
	<base href="http://cscms.travis/">
	<link href="/themes/CleverStyle/img/favicon.ico" rel="shortcut icon">
	<script class="cs-config" target="cs" type="application/json">{"base_url":"http:\/\/cscms.travis","current_base_url":"http:\/\/cscms.travis\/System","public_key":"%s","module":"System","in_admin":0,"is_admin":0,"is_user":0,"is_guest":1,"password_min_length":4,"password_min_strength":%d,"debug":0,"route":[],"route_path":["blank"],"route_ids":[]}</script>
	<link href="/storage/pcache/_CleverStyle_en.css?%s" rel="stylesheet" shim-shadowdom>
	<meta content="Web-site | Home" property="og:title">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
</head>

<body unresolved>

	<header>
		<div>
			<a href="/" class="cs-text-lead">Web-site</a>
			<nav>
				<a href="/">Home</a>
			</nav>
			<cs-cleverstyle-header-user-block avatar="/includes/img/guest.svg" username="Guest" login="guest"></cs-cleverstyle-header-user-block>
		</div>
		<hr>
		%w
	</header>
	<div id="body">
		<aside id="left_blocks">

		</aside>
		<aside id="right_blocks">

		</aside>
		<aside id="top_blocks">

		</aside>
		<div id="main_content">
			<div>

			</div>
		</div>
		<aside id="bottom_blocks">

		</aside>
	</div>
	<footer>
		<div>
			Page generated in %f s; %d queries to DB in %f s; memory consumption %f MiB (peak %f MiB)
		</div>
		<div>
			© Powered by <a target="_blank" href="http://cleverstyle.org/cms" title="CleverStyle CMS">CleverStyle CMS</a>
		</div>
	</footer>
	<script src="/storage/pcache/webcomponents.js?%s"></script>
	<script src="/storage/pcache/_CleverStyle_en.js?%s"></script>
	<link href="/storage/pcache/_CleverStyle_en.html?%s" rel="import">

</body>
