--FILE--
<?php
namespace cs;
require_once __DIR__.'/../../functions.php';
define('PUBLIC_CACHE', make_tmp_dir());
include __DIR__.'/../../bootstrap.php';
class Page_test extends Page {
	public static function test () {
		$Config = Config::instance();

		var_dump('No compression, head');
		Response::instance_reset();
		Request::instance_reset();
		clean_tmp_dir(PUBLIC_CACHE);
		$Config->core['cache_compress_js_css'] = 0;
		$Config->core['put_js_after_body']     = 0;
		Page::instance_reset();
		$Page                                  = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('No compression, after body');
		Response::instance_reset();
		Request::instance_reset();
		clean_tmp_dir(PUBLIC_CACHE);
		$Config->core['cache_compress_js_css'] = 0;
		$Config->core['put_js_after_body']     = 1;
		Page::instance_reset();
		$Page                                  = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Compression, no load optimization, head');
		Response::instance_reset();
		Request::instance_reset();
		clean_tmp_dir(PUBLIC_CACHE);
		$Config->core['cache_compress_js_css']      = 1;
		$Config->core['put_js_after_body']          = 0;
		$Config->core['frontend_load_optimization'] = 0;
		Page::instance_reset();
		$Page                                       = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Compression, no load optimization, after body');
		Response::instance_reset();
		Request::instance_reset();
		clean_tmp_dir(PUBLIC_CACHE);
		$Config->core['cache_compress_js_css']      = 1;
		$Config->core['put_js_after_body']          = 1;
		$Config->core['frontend_load_optimization'] = 0;
		Page::instance_reset();
		$Page                                       = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Compression, load optimization, head');
		Response::instance_reset();
		Request::instance_reset();
		clean_tmp_dir(PUBLIC_CACHE);
		$Config->core['cache_compress_js_css']      = 1;
		$Config->core['put_js_after_body']          = 0;
		$Config->core['frontend_load_optimization'] = 1;
		Page::instance_reset();
		$Page                                       = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Compression, load optimization, after body');
		Response::instance_reset();
		Request::instance_reset();
		clean_tmp_dir(PUBLIC_CACHE);
		$Config->core['cache_compress_js_css']      = 1;
		$Config->core['put_js_after_body']          = 1;
		$Config->core['frontend_load_optimization'] = 1;
		Page::instance_reset();
		$Page                                       = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Edge 14');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		Request::instance()->headers['user-agent'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.79 Safari/537.36 Edge/14.14928';
		$Page                                      = Page::instance();
		$Page->add_includes_on_page();
		var_dump('post_Body', $Page->post_Body);

		var_dump('Shadow DOM supported');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		Request::instance()->cookie['shadow_dom'] = 1;
		$Page                                     = Page::instance();
		$Page->add_includes_on_page();
		var_dump('post_Body', $Page->post_Body);

		var_dump('Already pushed');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		Request::instance()->cookie['pushed'] = 1;
		Page::instance()->add_includes_on_page();
		var_dump('headers', Response::instance()->headers);

		var_dump('Custom styles, scripts, html imports');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		$Page = Page::instance();
		$Page
			->html('')
			->html('import.html')
			->css('style.css')
			->js('script.js')
			->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Config not initialized');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		Config::instance_replace(False_class::instance());
		$Page = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Load includes of dependency');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		Config::instance_reset();
		Config::instance();
		$structure                   = file_get_json(PUBLIC_CACHE.'/CleverStyle.json');
		$structure[0]['System'][]    = 'dependency1';
		$structure[1]['dependency1'] = [
			'html' => '/dependency1.html',
			'js'   => '/dependency1.js',
			'css'  => '/dependency1.css'
		];
		file_put_json(PUBLIC_CACHE.'/CleverStyle.json', $structure);
		$Page = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);

		var_dump('Load includes of dependency (optimized)');
		Response::instance_reset();
		Request::instance_reset();
		Page::instance_reset();
		Config::instance_reset();
		Config::instance();
		$structure                         = file_get_json(PUBLIC_CACHE.'/CleverStyle.optimized.json');
		$structure[0]['/dependency1.html'] = 2;
		$structure[0]['/dependency1.js']   = 3;
		file_put_json(PUBLIC_CACHE.'/CleverStyle.optimized.json', $structure);
		$Page = Page::instance();
		$Page->add_includes_on_page();
		var_dump('Head', $Page->Head);
		var_dump('post_Body', $Page->post_Body);
		var_dump('headers', Response::instance()->headers);
	}
}
Page_test::test();
?>
--EXPECTF--
string(20) "No compression, head"
string(4) "Head"
string(%d) "<script src="/includes/js/WebComponents-polyfill/webcomponents-custom.min.js"></script>
<link href="/includes/css/unresolved.css?%s" rel="stylesheet">
<link href="/themes/CleverStyle/css/app-shell.css?%s" rel="stylesheet">
<link href="/themes/CleverStyle/css/style.css?%s" rel="stylesheet">
<script class="cs-config" target="cs.Language" type="application/json">
	%s
</script>
<script class="cs-config" target="requirejs.paths" type="application/json">{"System":"\/modules\/System\/includes\/js","system":"\/modules\/System\/includes\/js"}</script>
<script src="/includes/js/a0.config.js?%s"></script>
<script src="/includes/js/a1.Event.js?%s"></script>
<script src="/includes/js/a1.Language.js?%s"></script>
<script src="/includes/js/functions.js?%s"></script>
<script src="/includes/js/Polymer/a0.config.js?%s"></script>
<script src="/includes/js/Polymer/a0.hacks.js?%s"></script>
<script src="/includes/js/Polymer/a1.behaviors.js?%s"></script>
<script src="/includes/js/Polymer/a2.polymer-1.6.1+no-shady-dom.min.js?%s"></script>
<script src="/includes/js/Polymer/cs-unresolved.js?%s"></script>
<script src="/includes/js/Polymer/default-computed-bindings.js?%s"></script>
<script src="/includes/js/Polymer/extend-override.js?%s"></script>
<script src="/includes/js/Polymer/simplified-default-value-declaration.js?%s"></script>
<script src="/includes/js/zz0.alameda.js?%s"></script>
<script src="/includes/js/zz1.alameda-setup.js?%s"></script>
<script src="/includes/js/zzz.optimized-includes.js?%s"></script>
<link href="/includes/html/a0.advanced-styles-alone.html?%s" rel="import">
<link href="/includes/html/a0.basic-styles-alone.html?%s" rel="import">
<link href="/includes/html/a0.normalize.html?%s" rel="import">
<link href="/includes/html/a1.basic-styles.html?%s" rel="import">
<link href="/includes/html/a2.advanced-styles.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-0-behaviors-&-mixins/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-button/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-form/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-icon/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-input-text/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-label-button/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-label-switcher/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-link-button/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-button-group/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-dropdown/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-pagination/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-tabs/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-notify/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-progress/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-section-modal/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-section-switcher/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-select/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-textarea/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-tooltip/index.html?%s" rel="import">
<link href="/includes/html/cs-system-change-password/index.html?%s" rel="import">
<link href="/includes/html/cs-system-registration/index.html?%s" rel="import">
<link href="/includes/html/cs-system-restore-password/index.html?%s" rel="import">
<link href="/includes/html/cs-system-sign-in/index.html?%s" rel="import">
<link href="/includes/html/cs-system-user-settings/index.html?%s" rel="import">
<link href="/includes/html/iron-flex-layout.html?%s" rel="import">
<link href="/themes/CleverStyle/html/a0.css-variables.html?%s" rel="import">
<link href="/themes/CleverStyle/html/a1.main-styles.html?%s" rel="import">
<link href="/themes/CleverStyle/html/cs-cleverstyle-header-user-block/index.html?%s" rel="import">
<link href="/themes/CleverStyle/html/widgets styling.html?%s" rel="import">
"
string(9) "post_Body"
string(0) ""
string(7) "headers"
array(1) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
}
string(26) "No compression, after body"
string(4) "Head"
string(%d) "<link href="/includes/css/unresolved.css?%s" rel="stylesheet">
<link href="/themes/CleverStyle/css/app-shell.css?%s" rel="stylesheet">
<link href="/themes/CleverStyle/css/style.css?%s" rel="stylesheet">
<script class="cs-config" target="cs.Language" type="application/json">
	%s
</script>
<script class="cs-config" target="requirejs.paths" type="application/json">{"System":"\/modules\/System\/includes\/js","system":"\/modules\/System\/includes\/js"}</script>
"
string(9) "post_Body"
string(%d) "<script src="/includes/js/WebComponents-polyfill/webcomponents-custom.min.js"></script>
<script src="/includes/js/a0.config.js?%s"></script>
<script src="/includes/js/a1.Event.js?%s"></script>
<script src="/includes/js/a1.Language.js?%s"></script>
<script src="/includes/js/functions.js?%s"></script>
<script src="/includes/js/Polymer/a0.config.js?%s"></script>
<script src="/includes/js/Polymer/a0.hacks.js?%s"></script>
<script src="/includes/js/Polymer/a1.behaviors.js?%s"></script>
<script src="/includes/js/Polymer/a2.polymer-1.6.1+no-shady-dom.min.js?%s"></script>
<script src="/includes/js/Polymer/cs-unresolved.js?%s"></script>
<script src="/includes/js/Polymer/default-computed-bindings.js?%s"></script>
<script src="/includes/js/Polymer/extend-override.js?%s"></script>
<script src="/includes/js/Polymer/simplified-default-value-declaration.js?%s"></script>
<script src="/includes/js/zz0.alameda.js?%s"></script>
<script src="/includes/js/zz1.alameda-setup.js?%s"></script>
<script src="/includes/js/zzz.optimized-includes.js?%s"></script>
<link href="/includes/html/a0.advanced-styles-alone.html?%s" rel="import">
<link href="/includes/html/a0.basic-styles-alone.html?%s" rel="import">
<link href="/includes/html/a0.normalize.html?%s" rel="import">
<link href="/includes/html/a1.basic-styles.html?%s" rel="import">
<link href="/includes/html/a2.advanced-styles.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-0-behaviors-&-mixins/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-button/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-form/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-icon/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-input-text/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-label-button/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-label-switcher/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-link-button/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-button-group/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-dropdown/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-pagination/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-nav-tabs/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-notify/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-progress/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-section-modal/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-section-switcher/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-select/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-textarea/index.html?%s" rel="import">
<link href="/includes/html/CleverStyle Widgets/cs-tooltip/index.html?%s" rel="import">
<link href="/includes/html/cs-system-change-password/index.html?%s" rel="import">
<link href="/includes/html/cs-system-registration/index.html?%s" rel="import">
<link href="/includes/html/cs-system-restore-password/index.html?%s" rel="import">
<link href="/includes/html/cs-system-sign-in/index.html?%s" rel="import">
<link href="/includes/html/cs-system-user-settings/index.html?%s" rel="import">
<link href="/includes/html/iron-flex-layout.html?%s" rel="import">
<link href="/themes/CleverStyle/html/a0.css-variables.html?%s" rel="import">
<link href="/themes/CleverStyle/html/a1.main-styles.html?%s" rel="import">
<link href="/themes/CleverStyle/html/cs-cleverstyle-header-user-block/index.html?%s" rel="import">
<link href="/themes/CleverStyle/html/widgets styling.html?%s" rel="import">
"
string(7) "headers"
array(1) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
}
string(39) "Compression, no load optimization, head"
string(4) "Head"
string(%d) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(9) "post_Body"
string(0) ""
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(3) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
  }
}
string(45) "Compression, no load optimization, after body"
string(4) "Head"
string(%d) "<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
"
string(9) "post_Body"
string(%d) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(3) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
  }
}
string(36) "Compression, load optimization, head"
string(4) "Head"
string(%d) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
<script class="cs-config" target="cs.optimized_includes" type="application/json">[[],[]]</script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(9) "post_Body"
string(0) ""
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(3) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
  }
}
string(42) "Compression, load optimization, after body"
string(4) "Head"
string(%d) "<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
<script class="cs-config" target="cs.optimized_includes" type="application/json">[[],[]]</script>
"
string(9) "post_Body"
string(%d) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(3) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
  }
}
string(7) "Edge 14"
string(9) "post_Body"
string(265) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<script src="includes/js/microsoft_sh*t/14/URL.js"></script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(20) "Shadow DOM supported"
string(9) "post_Body"
string(141) "<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(14) "Already pushed"
string(7) "headers"
array(0) {
}
string(36) "Custom styles, scripts, html imports"
string(4) "Head"
string(341) "<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<link href="style.css" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
<script class="cs-config" target="cs.optimized_includes" type="application/json">[[],[]]</script>
"
string(9) "post_Body"
string(277) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<script src="script.js"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
<link href="import.html" rel="import">
"
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(4) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
    [3]=>
    string(34) "<style.css>; rel=preload; as=style"
  }
}
string(22) "Config not initialized"
string(4) "Head"
string(0) ""
string(9) "post_Body"
string(0) ""
string(7) "headers"
array(0) {
}
string(27) "Load includes of dependency"
string(4) "Head"
string(348) "<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<link href="/dependency1.css" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
<script class="cs-config" target="cs.optimized_includes" type="application/json">[[],[]]</script>
"
string(9) "post_Body"
string(289) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<script src="/dependency1.js"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
<link href="/dependency1.html" rel="import">
"
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(4) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
    [3]=>
    string(41) "</dependency1.css>; rel=preload; as=style"
  }
}
string(39) "Load includes of dependency (optimized)"
string(4) "Head"
string(386) "<link href="/storage/pcache/CleverStyle:System.css?%s" rel="stylesheet">
<link href="/dependency1.css" rel="stylesheet">
<script class="cs-config" target="cs.current_language" type="application/json">{"language":"English","hash":"%s"}</script>
<script class="cs-config" target="cs.optimized_includes" type="application/json">[["\/dependency1.js"],["\/dependency1.html"]]</script>
"
string(9) "post_Body"
string(204) "<script src="/storage/pcache/webcomponents.js?%s"></script>
<script src="/storage/pcache/CleverStyle:System.js?%s"></script>
<link href="/storage/pcache/CleverStyle:System.html?%s" rel="import">
"
string(7) "headers"
array(2) {
  ["set-cookie"]=>
  array(1) {
    [0]=>
    string(47) "pushed=1; path=/; domain=cscms.travis; HttpOnly"
  }
  ["link"]=>
  array(4) {
    [0]=>
    string(73) "</storage/pcache/CleverStyle:System.html?%s>; rel=preload; as=document"
    [1]=>
    string(69) "</storage/pcache/CleverStyle:System.js?%s>; rel=preload; as=script"
    [2]=>
    string(69) "</storage/pcache/CleverStyle:System.css?%s>; rel=preload; as=style"
    [3]=>
    string(41) "</dependency1.css>; rel=preload; as=style"
  }
}
