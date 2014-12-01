--TEST--
User profile page rendering
--FILE--
<?php
namespace cs;
include __DIR__.'/../custom_loader.php';
include __DIR__.'/../_SERVER.php';
// Simulate regular initialization
$_SERVER['REQUEST_URI']	= '/Profile/admin';
Language::instance();
Index::instance();
shutdown_function(false);
?>
--EXPECTF--
<!doctype html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# profile: http://ogp.me/ns/profile#">
	<title>Web-site | System | Profile of user admin</title>
	<meta charset="utf-8">
	<meta content="CleverStyle CMS by Mokrynskyi Nazar" name="generator">
	<base href="http://cscms.travis/">
	<link href="/CleverStyle/img/favicon.ico" rel="shortcut icon">
	<template class="cs-config" target="cs"><!--{"base_url":"http:\/\/cscms.travis","current_base_url":"http:\/\/cscms.travis\/System","public_key":"%s","module":"System","in_admin":0,"is_admin":0,"is_user":0,"is_guest":1,"debug":0,"cookie_prefix":"","cookie_domain":"cscms.travis","cookie_path":"\/","protocol":"http","route":["profile","info","admin"],"route_path":["profile","info","admin"],"route_ids":[]}--></template>
	<template class="cs-config" target="cs.rules_text"><!--"<p>Site rules<\/p>"--></template>
	<link href="/storage/pcache/_CleverStyle_en.css?%s" rel="stylesheet">
	<meta content="profile" property="og:type">
	<meta content="admin" property="profile:username">
	<meta content="/includes/img/guest.svg" property="og:image">
	<meta content="Web-site | System | Profile of user admin" property="og:title">
	<meta content="http://cscms.travis/System/profile/info/admin" property="og:url">
	<meta content="Web-site" property="og:site_name">
	<link href="/includes/img/guest.svg" rel="image_src">
</head>

<body>

	<header class="uk-navbar uk-navbar-attached">
		<div class="uk-navbar-content uk-navbar-flip">
			<img alt="" src="/includes/img/guest.svg">
			<div class="cs-header-guest-form active">
				<b>Hello, Guest!</b>
				<div>
					<button class="uk-button cs-button-compact cs-header-sign-in-slide uk-icon-sign-in" type="button">Sign in</button>
					<button class="uk-button cs-button-compact cs-header-registration-slide uk-icon-pencil" data-uk-tooltip="{animation:true,delay:200}" title="Quick registration form" type="button">Sign up</button>
				</div>
			</div>
			<div class="cs-header-restore-password-form">
				<input autocapitalize="off" autocorrect="off" class="cs-header-restore-password-email" placeholder="Login or e-mail" tabindex="1" type="text">
				<br>
				<button class="uk-button cs-button-compact cs-header-restore-password-process uk-button uk-icon-question" tabindex="2" type="button">Restore password</button>
				<button class="uk-button cs-button-compact uk-button cs-header-back" data-uk-tooltip="{animation:true,delay:200}" tabindex="3" title="Back" type="button">
					<span class=" uk-icon-chevron-down"></span>
				</button>
			</div>
			<div class="cs-header-registration-form">
				<input autocapitalize="off" autocorrect="off" class="cs-header-registration-email" placeholder="Email" tabindex="1" type="email">
				<br>
				<button class="uk-button cs-button-compact cs-header-registration-process uk-button uk-icon-pencil" tabindex="2" type="button">Sign up</button>
				<button class="uk-button cs-button-compact cs-header-back" data-uk-tooltip="{animation:true,delay:200}" tabindex="4" title="Back" type="button">
					<span class=" uk-icon-chevron-down"></span>
				</button>
			</div>
			<form class="cs-header-sign-in-form" method="post">
				<input autocapitalize="off" autocorrect="off" class="cs-header-sign-in-email" placeholder="Login or e-mail" tabindex="1" type="text">
				<input class="cs-header-user-password" placeholder="Password" tabindex="2" type="password">
				<br>
				<button class="uk-button cs-button-compact uk-icon-sign-in" tabindex="3" type="submit">Sign in</button>
				<button class="uk-button cs-button-compact cs-header-back" data-uk-tooltip="{animation:true,delay:200}" tabindex="5" title="Back" type="button">
					<span class=" uk-icon-chevron-down"></span>
				</button>
				<button class="uk-button cs-button-compact cs-header-restore-password-slide" data-uk-tooltip="{animation:true,delay:200}" tabindex="4" title="Restore password" type="button">
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
				<div layout horizontal>
					<div class="cs-profile-avatar">
						<img alt="admin" src="/includes/img/guest.svg" title="admin">
					</div>
					<div flex>
						<h1>Profile of user admin</h1>
						<cs-table right-left>
							<cs-table-row>
								<cs-table-cell>
									<h2>Registration date:</h2>
								</cs-table-cell>
								<cs-table-cell>
									<h2>%d %s %d</h2>
								</cs-table-cell>
							</cs-table-row>
						</cs-table>
					</div>
				</div>

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
