--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Cache  = Cache::instance();
$Config = Config::instance();
$L      = Language::instance();
$Text   = Text::instance();
$group  = 'test_group';
$label  = 'test_label';

var_dump('Set text');
$result = $Text->set(0, $group, $label, 'Text test text');
var_dump($result);

var_dump('Process text');
var_dump($Text->process(0, $result));
var_dump($Text->process(0, [$result]));

var_dump('Process and store in cache');
var_dump($Cache->get("texts/0/".mb_substr($result, 2, -1)."_$L->clang"));
var_dump($Text->process(0, $result, true));
var_dump($Cache->get("texts/0/".mb_substr($result, 2, -1)."_$L->clang"));

var_dump('Delete');
var_dump($Text->del(0, $group, $label));
var_dump($Text->process(0, $result));
var_dump($Cache->get("texts/0/".mb_substr($result, 2, -1)."_$L->clang"));

$Config->core['multilingual']     = 1;
$Config->core['active_languages'] = [
	'English',
	'Russian',
	'Ukrainian'
];

var_dump('Set text (multilingual, English)');
$result = $Text->set(0, $group, $label, 'Text test text en');
var_dump($result);

var_dump('Process text (multilingual, English)');
var_dump($Text->process(0, $result));
var_dump($Text->process(0, [$result]));

var_dump('Process and store in cache (multilingual, English)');
var_dump($Cache->get("texts/0/".mb_substr($result, 2, -1)."_$L->clang"));
var_dump($Text->process(0, $result, true));
var_dump($Cache->get("texts/0/".mb_substr($result, 2, -1)."_$L->clang"));

var_dump('Process text (multilingual, Ukrainian)');
$L->change('Ukrainian');
var_dump($Text->process(0, $result));

var_dump('Set text (multilingual, Ukrainian)');
var_dump($Text->set(0, $group, $label, 'Text test text uk'));
var_dump($Text->process(0, $result));

var_dump('Process text (multilingual, English again)');
$L->change('English');
var_dump($Text->process(0, $result));

var_dump('Update text (multilingual, English)');
var_dump($Text->set(0, $group, $label, 'Text test text en #2'));
var_dump($Text->process(0, $result));

var_dump('Delete (multilingual)');
var_dump($Text->del(0, $group, $label));
var_dump($Text->process(0, $result));

var_dump('Setting result of other element is not allowed');
var_dump($Text->set(0, $group, $label, $result));
?>
--EXPECT--
string(8) "Set text"
string(14) "Text test text"
string(12) "Process text"
string(14) "Text test text"
array(1) {
  [0]=>
  string(14) "Text test text"
}
string(26) "Process and store in cache"
bool(false)
string(14) "Text test text"
bool(false)
string(6) "Delete"
bool(true)
string(14) "Text test text"
bool(false)
string(32) "Set text (multilingual, English)"
string(6) "{¶11}"
string(36) "Process text (multilingual, English)"
string(17) "Text test text en"
array(1) {
  [0]=>
  string(17) "Text test text en"
}
string(50) "Process and store in cache (multilingual, English)"
bool(false)
string(17) "Text test text en"
string(17) "Text test text en"
string(38) "Process text (multilingual, Ukrainian)"
string(17) "Text test text en"
string(34) "Set text (multilingual, Ukrainian)"
string(6) "{¶11}"
string(17) "Text test text uk"
string(42) "Process text (multilingual, English again)"
string(17) "Text test text en"
string(35) "Update text (multilingual, English)"
string(6) "{¶11}"
string(20) "Text test text en #2"
string(21) "Delete (multilingual)"
bool(true)
string(0) ""
string(46) "Setting result of other element is not allowed"
bool(false)
