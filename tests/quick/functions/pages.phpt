--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
Config::instance_stub(
	[],
	[
		'base_url' => function () {
			return 'http://cscms.travis';
		}
	]
);
Page::instance_stub(
	[],
	[
		'link'          => function (...$arguments) {
			var_dump('cs\Page::link() called with', $arguments);
		},
		'canonical_url' => function ($url) {
			var_dump("cs\\Page::canonical_url('$url') called");
		}
	]
);

var_dump('page 1/5, string formatting');
var_dump(pages(1, 5, '/page/%d'));

var_dump('page 3/20, string formatting');
var_dump(pages(3, 20, '/page/%d'));

var_dump('page 17/20, string formatting');
var_dump(pages(17, 20, '/page/%d'));

var_dump('page 10/20, string formatting, absolute URL');
var_dump(pages(10, 20, 'http://example.com/page/%d'));

var_dump('page 10/20, callback, head links');
var_dump(
	pages(
		10,
		20,
		function ($page) {
			return $page == 1 ? '/' : "/page/$page";
		},
		true
	)
);

var_dump('Nothing to do with 1 page in total');
var_dump(pages(1, 1, '/page/%d'));
?>
--EXPECT--
string(27) "page 1/5, string formatting"
string(289) "<a is="cs-link-button" primary>1</a>
<a href="http://cscms.travis/page/2" is="cs-link-button">2</a>
<a href="http://cscms.travis/page/3" is="cs-link-button">3</a>
<a href="http://cscms.travis/page/4" is="cs-link-button">4</a>
<a href="http://cscms.travis/page/5" is="cs-link-button">5</a>
"
string(28) "page 3/20, string formatting"
string(650) "<a href="http://cscms.travis/page/1" is="cs-link-button">1</a>
<a href="http://cscms.travis/page/2" is="cs-link-button">2</a>
<a is="cs-link-button" primary>3</a>
<a href="http://cscms.travis/page/4" is="cs-link-button">4</a>
<a href="http://cscms.travis/page/5" is="cs-link-button">5</a>
<a href="http://cscms.travis/page/6" is="cs-link-button">6</a>
<a href="http://cscms.travis/page/7" is="cs-link-button">7</a>
<a disabled is="cs-link-button">...</a>
<a href="http://cscms.travis/page/18" is="cs-link-button">18</a>
<a href="http://cscms.travis/page/19" is="cs-link-button">19</a>
<a href="http://cscms.travis/page/20" is="cs-link-button">20</a>
"
string(29) "page 17/20, string formatting"
string(657) "<a href="http://cscms.travis/page/1" is="cs-link-button">1</a>
<a href="http://cscms.travis/page/2" is="cs-link-button">2</a>
<a href="http://cscms.travis/page/3" is="cs-link-button">3</a>
<a disabled is="cs-link-button">...</a>
<a href="http://cscms.travis/page/14" is="cs-link-button">14</a>
<a href="http://cscms.travis/page/15" is="cs-link-button">15</a>
<a href="http://cscms.travis/page/16" is="cs-link-button">16</a>
<a is="cs-link-button" primary>17</a>
<a href="http://cscms.travis/page/18" is="cs-link-button">18</a>
<a href="http://cscms.travis/page/19" is="cs-link-button">19</a>
<a href="http://cscms.travis/page/20" is="cs-link-button">20</a>
"
string(43) "page 10/20, string formatting, absolute URL"
string(622) "<a href="http://example.com/page/1" is="cs-link-button">1</a>
<a href="http://example.com/page/2" is="cs-link-button">2</a>
<a disabled is="cs-link-button">...</a>
<a href="http://example.com/page/8" is="cs-link-button">8</a>
<a href="http://example.com/page/9" is="cs-link-button">9</a>
<a is="cs-link-button" primary>10</a>
<a href="http://example.com/page/11" is="cs-link-button">11</a>
<a href="http://example.com/page/12" is="cs-link-button">12</a>
<a disabled is="cs-link-button">...</a>
<a href="http://example.com/page/19" is="cs-link-button">19</a>
<a href="http://example.com/page/20" is="cs-link-button">20</a>
"
string(32) "page 10/20, callback, head links"
string(27) "cs\Page::link() called with"
array(1) {
  [0]=>
  array(2) {
    ["href"]=>
    string(26) "http://cscms.travis/page/9"
    ["rel"]=>
    string(4) "prev"
  }
}
string(60) "cs\Page::canonical_url('http://cscms.travis/page/10') called"
string(27) "cs\Page::link() called with"
array(1) {
  [0]=>
  array(2) {
    ["href"]=>
    string(27) "http://cscms.travis/page/11"
    ["rel"]=>
    string(4) "next"
  }
}
string(624) "<a href="http://cscms.travis/" is="cs-link-button">1</a>
<a href="http://cscms.travis/page/2" is="cs-link-button">2</a>
<a disabled is="cs-link-button">...</a>
<a href="http://cscms.travis/page/8" is="cs-link-button">8</a>
<a href="http://cscms.travis/page/9" is="cs-link-button">9</a>
<a is="cs-link-button" primary>10</a>
<a href="http://cscms.travis/page/11" is="cs-link-button">11</a>
<a href="http://cscms.travis/page/12" is="cs-link-button">12</a>
<a disabled is="cs-link-button">...</a>
<a href="http://cscms.travis/page/19" is="cs-link-button">19</a>
<a href="http://cscms.travis/page/20" is="cs-link-button">20</a>
"
string(34) "Nothing to do with 1 page in total"
bool(false)
