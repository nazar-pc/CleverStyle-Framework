--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
var_dump('page 1/5, no formaction');
var_dump(pages_buttons(1, 5));

var_dump('page 3/20, string formatting');
var_dump(pages_buttons(3, 20, '/page/%d'));

var_dump('page 17/20, string formatting');
var_dump(pages_buttons(17, 20, '/page/%d'));

var_dump('page 10/20, string formatting');
var_dump(pages_buttons(10, 20, '/page/%d'));

var_dump('page 10/20, callback');
var_dump(
	pages_buttons(
		10,
		20,
		function ($page) {
			return $page == 1 ? '/' : "/page/$page";
		}
	)
);

var_dump('Nothing to do with 1 page in total');
var_dump(pages_buttons(1, 1, '/page/%d'));
?>
--EXPECT--
string(23) "page 1/5, no formaction"
string(404) "<button is="cs-button" name="page" primary type="button">1</button>
<button formaction="" is="cs-button" name="page" type="submit" value="2">2</button>
<button formaction="" is="cs-button" name="page" type="submit" value="3">3</button>
<button formaction="" is="cs-button" name="page" type="submit" value="4">4</button>
<button formaction="" is="cs-button" name="page" type="submit" value="5">5</button>
"
string(28) "page 3/20, string formatting"
string(967) "<button formaction="/page/1" is="cs-button" name="page" type="submit" value="1">1</button>
<button formaction="/page/2" is="cs-button" name="page" type="submit" value="2">2</button>
<button is="cs-button" name="page" primary type="button">3</button>
<button formaction="/page/4" is="cs-button" name="page" type="submit" value="4">4</button>
<button formaction="/page/5" is="cs-button" name="page" type="submit" value="5">5</button>
<button formaction="/page/6" is="cs-button" name="page" type="submit" value="6">6</button>
<button formaction="/page/7" is="cs-button" name="page" type="submit" value="7">7</button>
<button disabled is="cs-button" name="page" type="button">...</button>
<button formaction="/page/18" is="cs-button" name="page" type="submit" value="18">18</button>
<button formaction="/page/19" is="cs-button" name="page" type="submit" value="19">19</button>
<button formaction="/page/20" is="cs-button" name="page" type="submit" value="20">20</button>
"
string(29) "page 17/20, string formatting"
string(977) "<button formaction="/page/1" is="cs-button" name="page" type="submit" value="1">1</button>
<button formaction="/page/2" is="cs-button" name="page" type="submit" value="2">2</button>
<button formaction="/page/3" is="cs-button" name="page" type="submit" value="3">3</button>
<button disabled is="cs-button" name="page" type="button">...</button>
<button formaction="/page/14" is="cs-button" name="page" type="submit" value="14">14</button>
<button formaction="/page/15" is="cs-button" name="page" type="submit" value="15">15</button>
<button formaction="/page/16" is="cs-button" name="page" type="submit" value="16">16</button>
<button is="cs-button" name="page" primary type="button">17</button>
<button formaction="/page/18" is="cs-button" name="page" type="submit" value="18">18</button>
<button formaction="/page/19" is="cs-button" name="page" type="submit" value="19">19</button>
<button formaction="/page/20" is="cs-button" name="page" type="submit" value="20">20</button>
"
string(29) "page 10/20, string formatting"
string(951) "<button formaction="/page/1" is="cs-button" name="page" type="submit" value="1">1</button>
<button formaction="/page/2" is="cs-button" name="page" type="submit" value="2">2</button>
<button disabled is="cs-button" name="page" type="button">...</button>
<button formaction="/page/8" is="cs-button" name="page" type="submit" value="8">8</button>
<button formaction="/page/9" is="cs-button" name="page" type="submit" value="9">9</button>
<button is="cs-button" name="page" primary type="button">10</button>
<button formaction="/page/11" is="cs-button" name="page" type="submit" value="11">11</button>
<button formaction="/page/12" is="cs-button" name="page" type="submit" value="12">12</button>
<button disabled is="cs-button" name="page" type="button">...</button>
<button formaction="/page/19" is="cs-button" name="page" type="submit" value="19">19</button>
<button formaction="/page/20" is="cs-button" name="page" type="submit" value="20">20</button>
"
string(20) "page 10/20, callback"
string(945) "<button formaction="/" is="cs-button" name="page" type="submit" value="1">1</button>
<button formaction="/page/2" is="cs-button" name="page" type="submit" value="2">2</button>
<button disabled is="cs-button" name="page" type="button">...</button>
<button formaction="/page/8" is="cs-button" name="page" type="submit" value="8">8</button>
<button formaction="/page/9" is="cs-button" name="page" type="submit" value="9">9</button>
<button is="cs-button" name="page" primary type="button">10</button>
<button formaction="/page/11" is="cs-button" name="page" type="submit" value="11">11</button>
<button formaction="/page/12" is="cs-button" name="page" type="submit" value="12">12</button>
<button disabled is="cs-button" name="page" type="button">...</button>
<button formaction="/page/19" is="cs-button" name="page" type="submit" value="19">19</button>
<button formaction="/page/20" is="cs-button" name="page" type="submit" value="20">20</button>
"
string(34) "Nothing to do with 1 page in total"
bool(false)
