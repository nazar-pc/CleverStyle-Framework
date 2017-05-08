--FILE--
<?php
namespace cs\Page;
use function cs\make_tmp_dir;

include __DIR__.'/../../unit.php';
$tmp_dir = make_tmp_dir();
echo Assets_processing::html(file_get_contents(__DIR__.'/Assets_processing/my-element.html'), __DIR__.'/Assets_processing/my-element.html', $tmp_dir, false, $not_embedded_resources)."\n";
var_dump($not_embedded_resources);
var_dump('imported.js');
echo file_get_contents("$tmp_dir/d56902a9037da35c4d51753cb1b31d4f.js")."\n";
var_dump('imported-no-styles.js');
echo file_get_contents("$tmp_dir/b0c4817554bfb8e8f10f16ab1c683f47.js")."\n";
var_dump('my-element.js');
echo file_get_contents("$tmp_dir/1493da20bba0b80de0e0f7297d3cf6d0.js")."\n";
?>
--EXPECTF--

<dom-module id="my-element">
	<template><style>@import './23ade486f04787f66ae11b23a663c51b.css';@import url('./23ade486f04787f66ae11b23a663c51b.css');@import './fa302422c3063896a9294da61f07e969.css';@import url('./fa302422c3063896a9294da61f07e969.css');@import './23ade486f04787f66ae11b23a663c51b.css' screen and (orientation:landscape);@import url('./23ade486f04787f66ae11b23a663c51b.css') screen and (orientation:landscape);@import './fa302422c3063896a9294da61f07e969.css' screen and (orientation:landscape);@import url('./fa302422c3063896a9294da61f07e969.css') screen and (orientation:landscape);.some-class{background-color:#000;color:#fff;transition:opacity .3s,transform .5s;}.image{background-image:url('./b0baee9d279d34fa1dfd71aadb908c3f.svg');}.image-large{background-image:url('./0bf9edfe605a79ba7a8bea72b894729f.svg');}.image-absolute-path{background-image:url('./b0baee9d279d34fa1dfd71aadb908c3f.svg');}.image-query-string{background-image:url('./0bf9edfe605a79ba7a8bea72b894729f.svg');}@media(min-width:960px) and (orientation:landscape){.another-class{display:none;}}</style>
		<style>:host{display:block;}</style>
	</template>
%w
%w
</dom-module>
<script src="/external-script.js"></script>
<custom-style>
	<style>html{--my-property:black;}</style>
</custom-style>
<script src="./d56902a9037da35c4d51753cb1b31d4f.js"></script>
<custom-style>
	<style>html{--my-property-2:black;}</style>
</custom-style>
<script src="./b0c4817554bfb8e8f10f16ab1c683f47.js"></script>
<script src="/external-imported-script.js"></script>
<link rel="import" href="/external-import.html" type="html">
<script src="./1493da20bba0b80de0e0f7297d3cf6d0.js"></script>
array(14) {
  [0]=>
  string(%d) "/tests/%s/23ade486f04787f66ae11b23a663c51b.css"
  [1]=>
  string(%d) "/tests/%s/23ade486f04787f66ae11b23a663c51b.css"
  [2]=>
  string(%d) "/tests/%s/fa302422c3063896a9294da61f07e969.css"
  [3]=>
  string(%d) "/tests/%s/fa302422c3063896a9294da61f07e969.css"
  [4]=>
  string(%d) "/tests/%s/23ade486f04787f66ae11b23a663c51b.css"
  [5]=>
  string(%d) "/tests/%s/23ade486f04787f66ae11b23a663c51b.css"
  [6]=>
  string(%d) "/tests/%s/fa302422c3063896a9294da61f07e969.css"
  [7]=>
  string(%d) "/tests/%s/fa302422c3063896a9294da61f07e969.css"
  [8]=>
  string(%d) "/tests/%s/b0baee9d279d34fa1dfd71aadb908c3f.svg"
  [9]=>
  string(%d) "/tests/%s/0bf9edfe605a79ba7a8bea72b894729f.svg"
  [10]=>
  string(%d) "/tests/%s/b0baee9d279d34fa1dfd71aadb908c3f.svg"
  [11]=>
  string(70) "/tests/%s/d56902a9037da35c4d51753cb1b31d4f.js"
  [12]=>
  string(70) "/tests/%s/b0c4817554bfb8e8f10f16ab1c683f47.js"
  [13]=>
  string(70) "/tests/%s/1493da20bba0b80de0e0f7297d3cf6d0.js"
}
string(11) "imported.js"
var xyz = 'xyz';
string(21) "imported-no-styles.js"
var zyx = 'zyx';
string(13) "my-element.js"
Polymer({is : 'my-element'});;var bar = 'bar'; /* another comment */var foo = 'foo'; // Single-line after code
(function (bar, foo) {return foo + bar +(10 * 15 / 5);})(bar, foo);if ( !( bar > foo ) ){console . log (foo), console.log(bar
);}var script_code = "<script>JS here<\/script>";;Polymer.updateStyles();
