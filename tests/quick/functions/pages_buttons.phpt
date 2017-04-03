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
string(459) "<cs-button>
	<button name="page" primary type="button">1</button>
</cs-button>
<cs-button>
	<button formaction="" name="page" type="submit" value="2">2</button>
</cs-button>
<cs-button>
	<button formaction="" name="page" type="submit" value="3">3</button>
</cs-button>
<cs-button>
	<button formaction="" name="page" type="submit" value="4">4</button>
</cs-button>
<cs-button>
	<button formaction="" name="page" type="submit" value="5">5</button>
</cs-button>
"
string(28) "page 3/20, string formatting"
string(1088) "<cs-button>
	<button formaction="/page/1" name="page" type="submit" value="1">1</button>
</cs-button>
<cs-button>
	<button formaction="/page/2" name="page" type="submit" value="2">2</button>
</cs-button>
<cs-button>
	<button name="page" primary type="button">3</button>
</cs-button>
<cs-button>
	<button formaction="/page/4" name="page" type="submit" value="4">4</button>
</cs-button>
<cs-button>
	<button formaction="/page/5" name="page" type="submit" value="5">5</button>
</cs-button>
<cs-button>
	<button formaction="/page/6" name="page" type="submit" value="6">6</button>
</cs-button>
<cs-button>
	<button formaction="/page/7" name="page" type="submit" value="7">7</button>
</cs-button>
<cs-button>
	<button disabled name="page" type="button">...</button>
</cs-button>
<cs-button>
	<button formaction="/page/18" name="page" type="submit" value="18">18</button>
</cs-button>
<cs-button>
	<button formaction="/page/19" name="page" type="submit" value="19">19</button>
</cs-button>
<cs-button>
	<button formaction="/page/20" name="page" type="submit" value="20">20</button>
</cs-button>
"
string(29) "page 17/20, string formatting"
string(1098) "<cs-button>
	<button formaction="/page/1" name="page" type="submit" value="1">1</button>
</cs-button>
<cs-button>
	<button formaction="/page/2" name="page" type="submit" value="2">2</button>
</cs-button>
<cs-button>
	<button formaction="/page/3" name="page" type="submit" value="3">3</button>
</cs-button>
<cs-button>
	<button disabled name="page" type="button">...</button>
</cs-button>
<cs-button>
	<button formaction="/page/14" name="page" type="submit" value="14">14</button>
</cs-button>
<cs-button>
	<button formaction="/page/15" name="page" type="submit" value="15">15</button>
</cs-button>
<cs-button>
	<button formaction="/page/16" name="page" type="submit" value="16">16</button>
</cs-button>
<cs-button>
	<button name="page" primary type="button">17</button>
</cs-button>
<cs-button>
	<button formaction="/page/18" name="page" type="submit" value="18">18</button>
</cs-button>
<cs-button>
	<button formaction="/page/19" name="page" type="submit" value="19">19</button>
</cs-button>
<cs-button>
	<button formaction="/page/20" name="page" type="submit" value="20">20</button>
</cs-button>
"
string(29) "page 10/20, string formatting"
string(1072) "<cs-button>
	<button formaction="/page/1" name="page" type="submit" value="1">1</button>
</cs-button>
<cs-button>
	<button formaction="/page/2" name="page" type="submit" value="2">2</button>
</cs-button>
<cs-button>
	<button disabled name="page" type="button">...</button>
</cs-button>
<cs-button>
	<button formaction="/page/8" name="page" type="submit" value="8">8</button>
</cs-button>
<cs-button>
	<button formaction="/page/9" name="page" type="submit" value="9">9</button>
</cs-button>
<cs-button>
	<button name="page" primary type="button">10</button>
</cs-button>
<cs-button>
	<button formaction="/page/11" name="page" type="submit" value="11">11</button>
</cs-button>
<cs-button>
	<button formaction="/page/12" name="page" type="submit" value="12">12</button>
</cs-button>
<cs-button>
	<button disabled name="page" type="button">...</button>
</cs-button>
<cs-button>
	<button formaction="/page/19" name="page" type="submit" value="19">19</button>
</cs-button>
<cs-button>
	<button formaction="/page/20" name="page" type="submit" value="20">20</button>
</cs-button>
"
string(20) "page 10/20, callback"
string(1066) "<cs-button>
	<button formaction="/" name="page" type="submit" value="1">1</button>
</cs-button>
<cs-button>
	<button formaction="/page/2" name="page" type="submit" value="2">2</button>
</cs-button>
<cs-button>
	<button disabled name="page" type="button">...</button>
</cs-button>
<cs-button>
	<button formaction="/page/8" name="page" type="submit" value="8">8</button>
</cs-button>
<cs-button>
	<button formaction="/page/9" name="page" type="submit" value="9">9</button>
</cs-button>
<cs-button>
	<button name="page" primary type="button">10</button>
</cs-button>
<cs-button>
	<button formaction="/page/11" name="page" type="submit" value="11">11</button>
</cs-button>
<cs-button>
	<button formaction="/page/12" name="page" type="submit" value="12">12</button>
</cs-button>
<cs-button>
	<button disabled name="page" type="button">...</button>
</cs-button>
<cs-button>
	<button formaction="/page/19" name="page" type="submit" value="19">19</button>
</cs-button>
<cs-button>
	<button formaction="/page/20" name="page" type="submit" value="20">20</button>
</cs-button>
"
string(34) "Nothing to do with 1 page in total"
bool(false)
