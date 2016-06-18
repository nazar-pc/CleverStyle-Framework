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
	<link href="/storage/pcache/CleverStyle_en:System.css?%s" rel="stylesheet">
	<script class="cs-config" target="cs.optimized_includes" type="application/json">[["\/storage\/pcache\/jquery.js?%s"],[]]</script>
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
			<cs-cleverstyle-header-user-block avatar="http://cscms.travis/includes/img/guest.svg" guest username="Guest"></cs-cleverstyle-header-user-block>
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
			© Powered by <a target="_blank" href="http://cleverstyle.org/Framework" title="CleverStyle Framework">CleverStyle Framework</a>
		</div>
	</footer>
	<script src="/storage/pcache/webcomponents.js?%s"></script>
	<script src="/storage/pcache/CleverStyle_en:System.js?%s"></script>
	<link href="/storage/pcache/CleverStyle_en:System.html?%s" rel="import">

</body>
