--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
do_request();
echo Response::instance()->body;
?>
--EXPECTF--
<!doctype html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<title>Web-site | Home</title>
	<meta charset="utf-8">
	<meta content="CleverStyle Framework by Mokrynskyi Nazar" name="generator">
	<base href="http://cscms.travis/">
	<link href="/storage/public_cache/%s.css" rel="stylesheet">
	<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
	<script class="cs-config" target="cs.optimized_assets" type="application/json">[[],[]]</script>
	<link href="/favicon.ico" rel="shortcut icon">
	<meta content="Web-site | Home" property="og:title">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
</head>

<body cs-unresolved>

	<header>
		<div>
			<a href="/" class="cs-text-lead">Web-site</a>
			<nav>
				<a href="/">Home</a>
			</nav>
			<cs-cleverstyle-header-user-block avatar="http://cscms.travis/assets/img/guest.svg" guest username="Guest"></cs-cleverstyle-header-user-block>
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
			© Powered by <a target="_blank" href="https://cleverstyle.org/Framework" title="CleverStyle Framework">CleverStyle Framework</a>
		</div>
	</footer>
	<script src="/storage/public_cache/%s.js"></script>
	<script src="/storage/public_cache/%s.js"></script>
	<link href="/storage/public_cache/%s.html" rel="import">

</body>
