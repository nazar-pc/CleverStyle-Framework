--FILE--
<?php
namespace cs\Page;
use function cs\make_tmp_dir;

include __DIR__.'/../../unit.php';
$tmp_dir = make_tmp_dir();
echo Assets_processing::html(file_get_contents(__DIR__.'/Assets_processing/my-element.html'), __DIR__.'/Assets_processing/my-element.html', "$tmp_dir/System", false, $not_embedded_resources)."\n";
var_dump($not_embedded_resources);
var_dump('System-imported.js');
echo file_get_contents("$tmp_dir/System-imported.js")."\n";
var_dump('System-imported-no-styles.js');
echo file_get_contents("$tmp_dir/System-imported-no-styles.js")."\n";
var_dump('System.js');
echo file_get_contents("$tmp_dir/System.js")."\n";
?>
--EXPECTF--

<dom-module id="my-element">
	<template><style>.imported-class{color:black;}.imported-class{color:black;}.imported-class-2{color:black;}.imported-class-2{color:black;}@import '/tests/quick/Page/Assets_processing/imported.css?68b89' screen and (orientation:landscape);@import url('/tests/quick/Page/Assets_processing/imported.css?68b89') screen and (orientation:landscape);@import '/tests/quick/Page/Assets_processing/imported-2.css?047a1' screen and (orientation:landscape);@import url('/tests/quick/Page/Assets_processing/imported-2.css?047a1') screen and (orientation:landscape);.some-class{background-color:#000;color:#fff;transition:opacity .3s,transform .5s;}.image{background-image:url(data:image/svg+xml;charset=utf-8;base64,MTExMTE=);}.image-large{background-image:url('/tests/quick/Page/Assets_processing/image-large.svg?0bf9e');}.image-absolute-path{background-image:url("/image.svg");}.image-query-string{background-image:url('/tests/quick/Page/Assets_processing/image-large-2.svg?0bf9e');}@media(min-width:960px) and (orientation:landscape){.another-class{display:none;}}</style>
		<style>:host{display:block;}</style>
	</template>
%w
%w
</dom-module>
<script src="/external-script.js"></script>
<style is="custom-style">html{--my-property:black;}</style>
<script src="System-imported.js?d5690"></script>
<style is="custom-style">html{--my-property-2:black;}</style>
<script src="System-imported-no-styles.js?b0c48"></script>
<script src="/external-imported-script.js"></script>
<link rel="import" href="/external-import.html" type="html">
<script src="System.js?1493d"></script>
array(4) {
  [0]=>
  string(57) "/tests/quick/Page/Assets_processing/image-large.svg?%s"
  [1]=>
  string(24) "System-imported.js?d5690"
  [2]=>
  string(34) "System-imported-no-styles.js?b0c48"
  [3]=>
  string(15) "System.js?1493d"
}
string(18) "System-imported.js"
var xyz = 'xyz';
string(28) "System-imported-no-styles.js"
var zyx = 'zyx';
string(9) "System.js"
Polymer({is : 'my-element'});;var bar = 'bar'; /* another comment */var foo = 'foo'; // Single-line after code
(function (bar, foo) {return foo + bar +(10 * 15 / 5);})(bar, foo);if ( !( bar > foo ) ){console . log (foo), console.log(bar
);}var script_code = "<script>JS here<\/script>";;Polymer.updateStyles();
