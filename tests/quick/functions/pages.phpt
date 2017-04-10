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
string(369) "<cs-link-button primary>
	<a>1</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/2">2</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/3">3</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/4">4</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/5">5</a>
</cs-link-button>
"
string(28) "page 3/20, string formatting"
string(826) "<cs-link-button>
	<a href="http://cscms.travis/page/1">1</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/2">2</a>
</cs-link-button>
<cs-link-button primary>
	<a>3</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/4">4</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/5">5</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/6">6</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/7">7</a>
</cs-link-button>
<cs-link-button>
	<a disabled>...</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/18">18</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/19">19</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/20">20</a>
</cs-link-button>
"
string(29) "page 17/20, string formatting"
string(833) "<cs-link-button>
	<a href="http://cscms.travis/page/1">1</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/2">2</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/3">3</a>
</cs-link-button>
<cs-link-button>
	<a disabled>...</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/14">14</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/15">15</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/16">16</a>
</cs-link-button>
<cs-link-button primary>
	<a>17</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/18">18</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/19">19</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/20">20</a>
</cs-link-button>
"
string(43) "page 10/20, string formatting, absolute URL"
string(798) "<cs-link-button>
	<a href="http://example.com/page/1">1</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/2">2</a>
</cs-link-button>
<cs-link-button>
	<a disabled>...</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/8">8</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/9">9</a>
</cs-link-button>
<cs-link-button primary>
	<a>10</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/11">11</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/12">12</a>
</cs-link-button>
<cs-link-button>
	<a disabled>...</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/19">19</a>
</cs-link-button>
<cs-link-button>
	<a href="http://example.com/page/20">20</a>
</cs-link-button>
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
string(800) "<cs-link-button>
	<a href="http://cscms.travis/">1</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/2">2</a>
</cs-link-button>
<cs-link-button>
	<a disabled>...</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/8">8</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/9">9</a>
</cs-link-button>
<cs-link-button primary>
	<a>10</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/11">11</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/12">12</a>
</cs-link-button>
<cs-link-button>
	<a disabled>...</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/19">19</a>
</cs-link-button>
<cs-link-button>
	<a href="http://cscms.travis/page/20">20</a>
</cs-link-button>
"
string(34) "Nothing to do with 1 page in total"
bool(false)
