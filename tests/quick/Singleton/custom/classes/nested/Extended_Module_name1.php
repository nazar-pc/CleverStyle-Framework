<?php
namespace cs\custom\nested;
class Extended_Module_name1 extends _Extended_Module_name1 {
	function test () {
		parent::test();
		var_dump(self::class);
	}
}
