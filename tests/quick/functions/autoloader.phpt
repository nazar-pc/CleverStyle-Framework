--FILE--
<?php
namespace cs\Config {
	class Module_Properties {
		const ENABLED     = 1;
		const UNINSTALLED = -1;
	}
}
namespace cs {
	define('CORE', __DIR__.'/autoloader/core');
	define('MODULES', __DIR__.'/autoloader/modules');
	include __DIR__.'/../../unit.php';
	define('CACHE', make_tmp_dir());
	class Config {
		use
			Singleton;
	}
	class Request {
		public static $id = 0;
	}
	Config::instance_stub(
		[
			'components' => [
				'modules' => [
					'Test_module'             => [
						'active' => Config\Module_Properties::ENABLED
					],
					'Test_module_uninstalled' => [
						'active' => Config\Module_Properties::UNINSTALLED
					]
				]
			]
		]
	);

	@mkdir(CACHE.'/classes', 0770);
	file_put_json(
		CACHE."/classes/autoload",
		[
			'cs\Test_class_cached' => CORE.'/classes/Test_class_cached.php'
		]
	);
	file_put_json(
		CACHE."/classes/aliases",
		[
			'cs\modules\functionality_alias\Test_class_aliased_cached' => 'cs\modules\Test_module\Test_class_aliased_cached'
		]
	);

	var_dump('Autoload class');
	var_dump(class_exists('cs\Test_class'));

	var_dump('Autoload class (cached)');
	var_dump(class_exists('cs\Test_class_cached'));

	var_dump('Autoload class (non-existing)');
	var_dump(class_exists('cs\Test_class_non_existing'));

	var_dump('Autoload thirdparty');
	var_dump(class_exists('some\nested_namespace\Third_party_class'));

	var_dump('Autoload thirdparty (non-existing)');
	var_dump(class_exists('some\nested_namespace\Third_party_class_non_existing'));

	var_dump('Autoload trait');
	var_dump(class_exists('cs\Test_trait'));

	var_dump('Autoload trait (non-existing)');
	var_dump(class_exists('cs\Test_trait_non_existing'));

	var_dump('Autoload driver');
	var_dump(class_exists('cs\DB\Test_driver'));

	var_dump('Autoload driver (non-existing)');
	var_dump(class_exists('cs\DB\Test_driver_non_existing'));

	var_dump('Autoload module class');
	var_dump(class_exists('cs\modules\Test_module\Test_class'));

	var_dump('Autoload module class (non-existing)');
	var_dump(class_exists('cs\modules\Test_module\Test_class_non_existing'));

	var_dump('Autoload module class via functionality alias');
	var_dump(class_exists('cs\modules\functionality_alias\Test_class_aliased'));

	var_dump('Autoload module class via functionality alias (cached)');
	var_dump(class_exists('cs\modules\functionality_alias\Test_class_aliased_cached'));

	var_dump('Autoload module class via functionality alias (non-existing)');
	var_dump(class_exists('cs\modules\functionality_alias\Test_class_aliased_non_existing'));
}
?>
--EXPECT--
string(14) "Autoload class"
bool(true)
string(23) "Autoload class (cached)"
bool(true)
string(29) "Autoload class (non-existing)"
bool(false)
string(19) "Autoload thirdparty"
bool(true)
string(34) "Autoload thirdparty (non-existing)"
bool(false)
string(14) "Autoload trait"
bool(true)
string(29) "Autoload trait (non-existing)"
bool(false)
string(15) "Autoload driver"
bool(true)
string(30) "Autoload driver (non-existing)"
bool(false)
string(21) "Autoload module class"
bool(true)
string(36) "Autoload module class (non-existing)"
bool(false)
string(45) "Autoload module class via functionality alias"
bool(true)
string(54) "Autoload module class via functionality alias (cached)"
bool(true)
string(60) "Autoload module class via functionality alias (non-existing)"
bool(false)
