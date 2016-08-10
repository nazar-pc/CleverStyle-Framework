--FILE--
<?php
namespace cs\Page;
include __DIR__.'/../../unit.php';
echo Includes_processing::html(file_get_contents(__DIR__.'/my-element.html'), __DIR__.'/my-element.html', '', true, $not_embedded_resources)."\n";
var_dump($not_embedded_resources);
?>
--EXPECTF--

<dom-module id="my-element">
	<template><style>.imported-class{color:black;}.imported-class{color:black;}.imported-class-2{color:black;}.imported-class-2{color:black;}@import '/tests/quick/Page/imported.css?68b89' screen and (orientation:landscape);@import url('/tests/quick/Page/imported.css?68b89') screen and (orientation:landscape);@import '/tests/quick/Page/imported-2.css?047a1' screen and (orientation:landscape);@import url('/tests/quick/Page/imported-2.css?047a1') screen and (orientation:landscape);.some-class{background-color:#000;color:#fff;transition:opacity .3s,transform .5s;}.image{background-image:url(data:image/svg+xml;charset=utf-8;base64,MTExMTE=);}.image-large{background-image:url('/tests/quick/Page/image-large.svg?0bf9e');}.image-absolute-path{background-image:url("/image.svg");}.image-query-string{background-image:url('/tests/quick/Page/image-large-2.svg?0bf9e');}@media(min-width:960px) and (orientation:landscape){.another-class{display:none;}}</style>
		<style>:host{display:block;}</style>
	</template>
%w
%w
</dom-module>
<script src="/external-script.js"></script>
<style is="custom-style">:root{--my-property:black;}</style>
<style is="custom-style">:root{--my-property-2:black;}</style>
<script src="/external-imported-script.js"></script>
<link rel="import" href="/external-import.html" type="html">
<script>Polymer({is : 'my-element'});;var bar = 'bar'; /* another comment */var foo = 'foo'; // Single-line after code
(function (bar, foo) {return foo + bar +(10 * 15 / 5);})(bar, foo);if ( !( bar > foo ) ){console . log (foo), console.log(bar
);}var script_code = "<script>JS here<\/script>";;Polymer.updateStyles();;var xyz = 'xyz';;var zyx = 'zyx';</script>
array(1) {
  [0]=>
  string(39) "/tests/quick/Page/image-large.svg?0bf9e"
}
