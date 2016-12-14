--FILE--
<?php
namespace cs\Page;
use function cs\make_tmp_dir;

include __DIR__.'/../../unit.php';
$tmp_dir = make_tmp_dir();
echo Assets_processing::html(file_get_contents(__DIR__.'/Assets_processing/my-element.html'), __DIR__.'/Assets_processing/my-element.html', $tmp_dir, true, $not_embedded_resources)."\n";
var_dump($not_embedded_resources);
?>
--EXPECTF--

<dom-module id="my-element">
	<template><style>.imported-class{color:black;}.imported-class{color:black;}.imported-class-2{color:black;}.imported-class-2{color:black;}@import './23ade486f04787f66ae11b23a663c51b.css' screen and (orientation:landscape);@import url('./23ade486f04787f66ae11b23a663c51b.css') screen and (orientation:landscape);@import './fa302422c3063896a9294da61f07e969.css' screen and (orientation:landscape);@import url('./fa302422c3063896a9294da61f07e969.css') screen and (orientation:landscape);.some-class{background-color:#000;color:#fff;transition:opacity .3s,transform .5s;}.image{background-image:url(data:image/svg+xml;charset=utf-8;base64,MTExMTE=);}.image-large{background-image:url('./0bf9edfe605a79ba7a8bea72b894729f.svg');}.image-absolute-path{background-image:url("data:image/svg+xml;charset=utf-8;base64,MTExMTE=");}.image-query-string{background-image:url('./0bf9edfe605a79ba7a8bea72b894729f.svg');}@media(min-width:960px) and (orientation:landscape){.another-class{display:none;}}</style>
		<style>:host{display:block;}</style>
	</template>
%w
%w
</dom-module>
<script src="/external-script.js"></script>
<style is="custom-style">html{--my-property:black;}</style>
<style is="custom-style">html{--my-property-2:black;}</style>
<script src="/external-imported-script.js"></script>
<link rel="import" href="/external-import.html" type="html">
<script>Polymer({is : 'my-element'});;var bar = 'bar'; /* another comment */var foo = 'foo'; // Single-line after code
(function (bar, foo) {return foo + bar +(10 * 15 / 5);})(bar, foo);if ( !( bar > foo ) ){console . log (foo), console.log(bar
);}var script_code = "<script>JS here<\/script>";;Polymer.updateStyles();;var xyz = 'xyz';;var zyx = 'zyx';</script>
array(1) {
  [0]=>
  string(%d) "/tests/%s/0bf9edfe605a79ba7a8bea72b894729f.svg"
}
