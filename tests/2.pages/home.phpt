--TEST--
Home page rendering
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
// Simulate regular initialization
Language::instance();
Index::instance();
shutdown_function(true);
?>
--EXPECTF--
<!doctype html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb#">
	<title>Web-site | Home</title>
	<meta charset="utf-8">
	<meta content="CleverStyle CMS by Mokrynskyi Nazar" name="generator">
	<base href="http://cscms.travis/">
	<link href="/CleverStyle/img/favicon.ico" rel="shortcut icon">
	<template class="cs-config" target="cs"><!--{"base_url":"http:\/\/cscms.travis","current_base_url":"http:\/\/cscms.travis\/System","public_key":"%s","module":"System","in_admin":0,"is_admin":0,"is_user":0,"is_guest":1,"debug":0,"cookie_prefix":"","cookie_domain":"cscms.travis","cookie_path":"\/","protocol":"http","route":[],"route_path":["blank"],"route_ids":[]}--></template>
	<template class="cs-config" target="cs.rules_text"><!--"<p>Site rules<\/p>"--></template>
	<link href="/storage/pcache/_CleverStyle_en.css?%s" rel="stylesheet" shim-shadowdom="">
	<meta content="Web-site | Home" property="og:title">
	<meta content="http://cscms.travis" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<meta content="website" property="og:type">
</head>

<body unresolved>

	<header class="uk-navbar uk-navbar-attached">
		<div class="uk-navbar-content uk-navbar-flip">
			<img alt="" src="/includes/img/guest.svg">
			<div class="cs-header-guest-form active">
				<b>Hello, Guest!</b>
				<div>
					<button class="uk-button cs-button-compact cs-header-sign-in-slide" type="button">
						<span class=" uk-icon-sign-in"></span> Sign in
					</button>
					<button class="uk-button cs-button-compact cs-header-registration-slide" data-uk-tooltip="{animation:true,delay:200}" title="Quick registration form" type="button">
						<span class=" uk-icon-pencil"></span> Sign up
					</button>
				</div>
			</div>
			<div class="cs-header-restore-password-form">
				<input autocapitalize="off" autocorrect="off" class="cs-header-restore-password-email" placeholder="Login or e-mail" tabindex="1" type="text">
				<br>
				<button class="uk-button cs-button-compact cs-header-restore-password-process" tabindex="2" type="button">
					<span class=" uk-icon-question"></span> Restore password
				</button>
				<button class="uk-button cs-button-compact cs-header-back" data-uk-tooltip="{animation:true,delay:200}" title="Back" type="button">
					<span class=" uk-icon-chevron-down"></span>
				</button>
			</div>
			<div class="cs-header-registration-form">
				<input autocapitalize="off" autocorrect="off" class="cs-header-registration-email" placeholder="Email" type="email">
				<br>
				<button class="uk-button cs-button-compact cs-header-registration-process" type="button">
					<span class=" uk-icon-pencil"></span> Sign up
				</button>
				<button class="uk-button cs-button-compact cs-header-back" data-uk-tooltip="{animation:true,delay:200}" title="Back" type="button">
					<span class=" uk-icon-chevron-down"></span>
				</button>
			</div>
			<form class="cs-header-sign-in-form" method="post">
				<input autocapitalize="off" autocorrect="off" class="cs-header-sign-in-email" placeholder="Login or e-mail" type="text">
				<input class="cs-header-user-password" placeholder="Password" type="password">
				<br>
				<button class="uk-button cs-button-compact" type="submit">
					<span class=" uk-icon-sign-in"></span> Sign in
				</button>
				<button class="uk-button cs-button-compact cs-header-back" data-uk-tooltip="{animation:true,delay:200}" title="Back" type="button">
					<span class=" uk-icon-chevron-down"></span>
				</button>
				<button class="uk-button cs-button-compact cs-header-restore-password-slide" data-uk-tooltip="{animation:true,delay:200}" title="Restore password" type="button">
					<span class=" uk-icon-question"></span>
				</button>
				<input name="session" type="hidden" value="%s">
			</form>
		</div>
		<a href="/" class="uk-navbar-brand">Web-site</a>
		<ul class="uk-navbar-nav"><li><a href="/">Home</a></li></ul>
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
			Page generated in %f seconds, %d query(s) in DB in %f seconds, maximal memory consumption %f MB<sup>%f MB</sup>
		</div>
		<div>
			© Powered by <a target="_blank" href="http://cleverstyle.org/cms" title="CleverStyle CMS">CleverStyle CMS</a>
		</div>
	</footer>
	<script src="/storage/pcache/_CleverStyle_en.js?%s"></script>
	<link href="/storage/pcache/_CleverStyle_en.html?%s" rel="import">

</body>
