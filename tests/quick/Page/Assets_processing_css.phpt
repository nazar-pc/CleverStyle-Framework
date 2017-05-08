--FILE--
<?php
namespace cs\Page;
use function cs\make_tmp_dir;

include __DIR__.'/../../unit.php';
$tmp_dir = make_tmp_dir();
echo Assets_processing::css(file_get_contents(__DIR__.'/Assets_processing/style.css'), __DIR__.'/Assets_processing/style.css', $tmp_dir, $not_embedded_resources)."\n";
var_dump($not_embedded_resources);
?>
--EXPECTF--
@import './23ade486f04787f66ae11b23a663c51b.css';@import url('./23ade486f04787f66ae11b23a663c51b.css');@import './fa302422c3063896a9294da61f07e969.css';@import url('./fa302422c3063896a9294da61f07e969.css');@import './23ade486f04787f66ae11b23a663c51b.css' screen and (orientation:landscape);@import url('./23ade486f04787f66ae11b23a663c51b.css') screen and (orientation:landscape);@import './fa302422c3063896a9294da61f07e969.css' screen and (orientation:landscape);@import url('./fa302422c3063896a9294da61f07e969.css') screen and (orientation:landscape);.some-class{background-color:#000;color:#fff;transition:opacity .3s,transform .5s;}.image{background-image:url('./b0baee9d279d34fa1dfd71aadb908c3f.svg');}.image-large{background-image:url('./0bf9edfe605a79ba7a8bea72b894729f.svg');}.image-absolute-path{background-image:url('./b0baee9d279d34fa1dfd71aadb908c3f.svg');}.image-query-string{background-image:url('./0bf9edfe605a79ba7a8bea72b894729f.svg');}@media(min-width:960px) and (orientation:landscape){.another-class{display:none;}}
array(11) {
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
}
