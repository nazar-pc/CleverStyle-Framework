<?php
namespace cs\custom\nested;
class Extended_Module_name2 extends _Extended_Module_name2 {
	function test () {
		parent::test();
		var_dump(self::class);
	}
}
