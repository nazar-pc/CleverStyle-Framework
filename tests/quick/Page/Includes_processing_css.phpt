--FILE--
<?php
namespace cs\Page;
include __DIR__.'/../../unit.php';
echo Includes_processing::css(file_get_contents(__DIR__.'/style.css'), __DIR__.'/style.css', $not_embedded_resources)."\n";
var_dump($not_embedded_resources);
?>
--EXPECT--
.imported-class{color:black;}.imported-class-2{color:black;}.some-class{background-color:#000;color:#fff;transition:opacity .3s,transform .5s;}.image{background-image:url(data:image/svg+xml;charset=utf-8;base64,MTExMTE=);}.image-large{background-image:url(''/tests/quick/Page/image-large.svg?0bf9e'');}.image-absolute-path{background-image:url("/image.svg");}.image-query-string{background-image:url('/tests/quick/Page/image-large-2.svg?0bf9e');}@media(min-width:960px) and (orientation:landscape){.another-class{display:none;}}
array(1) {
  [0]=>
  string(39) "/tests/quick/Page/image-large.svg?0bf9e"
}
