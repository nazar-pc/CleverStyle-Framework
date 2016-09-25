--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';

Language::instance_stub(
	[
		'prop' => 'Property'
	],
	[
		'format' => function (...$arguments) {
			var_dump('cs\Language::format() called with', $arguments);
			return $arguments[0];
		}
	]
);

var_dump('__(prop)');
var_dump(__('prop'));

var_dump('__(prop, arguments)');
var_dump(__('prop', 'x', 'y'));
?>
--EXPECT--
string(8) "__(prop)"
string(8) "Property"
string(19) "__(prop, arguments)"
string(33) "cs\Language::format() called with"
array(3) {
  [0]=>
  string(4) "prop"
  [1]=>
  string(1) "x"
  [2]=>
  string(1) "y"
}
string(4) "prop"
