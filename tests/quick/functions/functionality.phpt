--FILE--
<?php
namespace cs;
include __DIR__.'/../../unit.php';
define('MODULES', __DIR__.'/functionality/modules');
Cache::instance_stub(
	[],
	[
		'get' => function ($item, $callable) {
			return $callable();
		}
	]
);
Config::instance_stub(
	[
		'components' => [
			'modules' => [
				'Blogs'    => [
					'active' => Config\Module_Properties::ENABLED
				],
				'Content'  => [
					'active' => Config\Module_Properties::ENABLED
				],
				'TinyMCE'  => [
					'active' => Config\Module_Properties::ENABLED
				],
				'Uploader' => [
					'active' => Config\Module_Properties::DISABLED
				]
			]
		]
	],
	[
		'module' => function ($module_name) {
			return new Config\Module_Properties(Config::instance()->components['modules'][$module_name], $module_name);
		}
	]
);

var_dump('Simple functionality');
var_dump(functionality('blogs'));

var_dump('Simple functionality (module not enabled)');
var_dump(functionality('file_upload'));

var_dump('Simple functionalities');
var_dump(functionality(['blogs', 'content', 'editor', 'simple_inline_editor']));

var_dump('Simple functionalities (one module not enabled');
var_dump(functionality(['blogs', 'content', 'editor', 'simple_inline_editor', 'file_upload']));
?>
--EXPECT--
string(20) "Simple functionality"
bool(true)
string(41) "Simple functionality (module not enabled)"
bool(false)
string(22) "Simple functionalities"
bool(true)
string(46) "Simple functionalities (one module not enabled"
bool(false)
