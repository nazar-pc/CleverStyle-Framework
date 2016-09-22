--FILE--
<?php
namespace cs\Singleton {
	function modified_classes ($updated_modified_classes = null) {
		static $modified_classes = [];
		if ($updated_modified_classes) {
			$modified_classes = $updated_modified_classes;
		}
		return $modified_classes;
	}
	function __classes_clean_cache () {
		var_dump('__classes_clean_cache() called');
	}
}
namespace cs {
	include __DIR__.'/../../code_coverage.php';
	class Request {
		static $id = 0;
	}
	require_once __DIR__.'/../../../core/classes/False_class.php';
	require_once __DIR__.'/../../../core/traits/Singleton/Base.php';
	require_once __DIR__.'/../../../core/traits/Singleton.php';

	class Example {
		use
			Singleton;
		const INIT_STATE_METHOD = 'init';
		protected function init () {
			var_dump(self::class.' reinitialization');
		}
	}

	class Example_custom {
		use
			Singleton;
		protected function construct () {
			var_dump(self::class.' constructed');
		}
	}

	class First {
		use
			Singleton;
		const INIT_STATE_METHOD = 'init';
		protected function init () {
			var_dump(static::class.'::init()');
		}
		protected function construct () {
			var_dump(static::class.'::construct() #1');
			Second::instance();
			var_dump(static::class.'::construct() #2');
		}
	}

	class Second extends First {
		protected function construct () {
			var_dump(static::class.'::construct() #1');
			First::instance();
			var_dump(static::class.'::construct() #2');
		}
	}
}
namespace cs\nested {
	define('CUSTOM', __DIR__.'/custom');

	class Extended {
		use
			\cs\Singleton;
		function test () {
			var_dump(self::class);
		}
	}

	class Extended2 {
		use
			\cs\Singleton;
		function test () {
			var_dump(self::class);
		}
	}
}
namespace cs\custom {
	class Example_custom {
		use
			\cs\Singleton;
		protected function construct () {
			var_dump(self::class.' constructed');
		}
	}
}
namespace {
	class Not_in_cs {
		use
			cs\Singleton;
	}
}
namespace cs {
	var_dump('Getting basic instance');
	var_dump(Example::instance(true));
	var_dump(Example::instance());
	var_dump(Example::instance(true));

	var_dump('Class reinitialization on new request');
	++Request::$id;
	var_dump(Example::instance());

	var_dump('Class not in cs namespace');
	var_dump(\Not_in_cs::instance());

	var_dump('Class in cs\custom namespace');
	var_dump(Example_custom::instance());

	var_dump('Multiple class extension');
	$Extended = nested\Extended::instance();
	var_dump($Extended);
	$Extended->test();

	var_dump('Multiple class extension non existing files');
	Singleton\modified_classes(
		[
			'cs\\nested\\Extended2' => [
				'aliases'     => [
					[
						'original' => 'cs\\nested\\Extended2',
						'alias'    => 'cs\\custom\\nested\\_Extended2_Module_name1',
						'path'     => CUSTOM.'/classes/nested/Extended2_Module_name1.php',
					],
					[
						'original' => 'cs\\custom\\nested\\Extended_Module_name1',
						'alias'    => 'cs\\custom\\nested\\_Extended_Module_name2',
						'path'     => CUSTOM.'/classes/nested/Extended2_Module_name2.php',
					],
				],
				'final_class' => 'cs\\custom\\nested\\Extended2_Module_name2',
			]
		]
	);
	$Extended2 = nested\Extended2::instance();
	var_dump($Extended2);
	$Extended2->test();

	First::instance();
}
?>
--EXPECTF--
string(22) "Getting basic instance"
object(cs\False_class)#%d (0) {
}
string(27) "cs\Example reinitialization"
object(cs\Example)#%d (1) {
  ["__request_id":"cs\Example":private]=>
  int(0)
}
object(cs\Example)#%d (1) {
  ["__request_id":"cs\Example":private]=>
  int(0)
}
string(37) "Class reinitialization on new request"
string(27) "cs\Example reinitialization"
object(cs\Example)#%d (1) {
  ["__request_id":"cs\Example":private]=>
  int(1)
}
string(25) "Class not in cs namespace"
object(cs\False_class)#%d (0) {
}
string(28) "Class in cs\custom namespace"
string(36) "cs\custom\Example_custom constructed"
object(cs\custom\Example_custom)#%d (1) {
  ["__request_id":"cs\custom\Example_custom":private]=>
  int(1)
}
string(24) "Multiple class extension"
object(cs\custom\nested\Extended_Module_name2)#%d (1) {
  ["__request_id":"cs\nested\Extended":private]=>
  int(1)
}
string(18) "cs\nested\Extended"
string(38) "cs\custom\nested\Extended_Module_name1"
string(38) "cs\custom\nested\Extended_Module_name2"
string(43) "Multiple class extension non existing files"
string(30) "__classes_clean_cache() called"
object(cs\nested\Extended2)#%d (1) {
  ["__request_id":"cs\nested\Extended2":private]=>
  int(1)
}
string(19) "cs\nested\Extended2"
string(24) "cs\First::construct() #1"
string(25) "cs\Second::construct() #1"
string(25) "cs\Second::construct() #2"
string(17) "cs\Second::init()"
string(24) "cs\First::construct() #2"
string(16) "cs\First::init()"
