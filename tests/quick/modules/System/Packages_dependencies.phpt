--FILE--
<?php
namespace cs\modules\System;
use
	cs\Config,
	cs\Config\Module_Properties,
	cs\Core;

define('MODULES', __DIR__.'/Packages_dependencies/modules');
require_once __DIR__.'/../../../../modules/System/Packages_dependencies.php';
include __DIR__.'/../../../unit.php';

Core::instance_stub(
	[
		'db_type'      => 'SQLite',
		'storage_type' => 'Local'
	]
);

$Config = Config::instance_stub(
	[
		'components' => [
			'modules' => [
				'Blogs'    => [
					'active' => Module_Properties::UNINSTALLED
				],
				'Comments' => [
					'active' => Module_Properties::UNINSTALLED
				],
				'System'   => [
					'active' => Module_Properties::ENABLED
				],
				'Tags'     => [
					'active' => Module_Properties::UNINSTALLED
				],
				'TinyMCE'  => [
					'active' => Module_Properties::UNINSTALLED
				]
			]
		],
		'db'         => [
			[
				'type' => 'PostgreSQL'
			]
		],
		'storage'    => [
			[
				'connection' => 'FTP'
			]
		]
	],
	[
		'module' => function ($module_name) use (&$Config) {
			return new Module_Properties($Config->components['modules'][$module_name], $module_name);
		}
	]
);

var_dump('Empty meta');
var_dump(Packages_dependencies::get_dependencies([]));

var_dump('Blogs install dependencies (nothing installed yet)');
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/Blogs/meta.json')));

var_dump('Tags install dependencies (nothing installed yet)');
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/Tags/meta.json')));

var_dump('Blogs dependencies (Tags installed)');
$Config->components['modules']['Tags']['active'] = Module_Properties::ENABLED;
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/Blogs/meta.json')));

var_dump('Comments dependencies (Blogs and Tags installed)');
$Config->components['modules']['Blogs']['active'] = Module_Properties::ENABLED;
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/Comments/meta.json')));

var_dump('Alternative blogs dependencies (Blogs and Tags installed)');
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/Alternative_blogs/meta.json')));

var_dump('Try to update Blogs to older version');
var_dump(Packages_dependencies::get_dependencies(['version' => '0.1'] + file_get_json(MODULES.'/Blogs/meta.json'), true));

var_dump('Try to update Blogs to the same version');
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/Blogs/meta.json'), true));

var_dump('Try to update Blogs to newer version');
var_dump(Packages_dependencies::get_dependencies(['version' => '99.5'] + file_get_json(MODULES.'/Blogs/meta.json'), true));

var_dump('Try to update Blogs to newer version (but newer existing version is needed)');
var_dump(Packages_dependencies::get_dependencies(['version' => '99.5', 'update_from_version' => '98.0'] + file_get_json(MODULES.'/Blogs/meta.json'), true));

var_dump('Try to install TinyMCE (requires newer version of the system than available)');
var_dump(Packages_dependencies::get_dependencies(file_get_json(MODULES.'/TinyMCE/meta.json')));

var_dump('Get dependent packages (empty meta)');
var_dump(Packages_dependencies::get_dependent_packages([]));

var_dump('Get packages dependent on System');
var_dump(Packages_dependencies::get_dependent_packages(file_get_json(MODULES.'/System/meta.json')));

var_dump('Get packages dependent on Tags');
var_dump(Packages_dependencies::get_dependent_packages(file_get_json(MODULES.'/Tags/meta.json')));

var_dump('Get packages dependent on Comments (not installed)');
var_dump(Packages_dependencies::get_dependent_packages(file_get_json(MODULES.'/Comments/meta.json')));

?>
--EXPECT--
string(10) "Empty meta"
array(0) {
}
string(50) "Blogs install dependencies (nothing installed yet)"
array(1) {
  ["require"]=>
  array(1) {
    [0]=>
    array(2) {
      ["package"]=>
      string(4) "Tags"
      ["required"]=>
      array(2) {
        [0]=>
        array(2) {
          [0]=>
          string(2) ">="
          [1]=>
          float(1)
        }
        [1]=>
        array(2) {
          [0]=>
          string(1) "<"
          [1]=>
          float(2)
        }
      }
    }
  }
}
string(49) "Tags install dependencies (nothing installed yet)"
array(0) {
}
string(35) "Blogs dependencies (Tags installed)"
array(0) {
}
string(48) "Comments dependencies (Blogs and Tags installed)"
array(4) {
  ["require"]=>
  array(1) {
    [0]=>
    array(2) {
      ["package"]=>
      string(5) "magic"
      ["required"]=>
      array(1) {
        [0]=>
        array(2) {
          [0]=>
          string(2) ">="
          [1]=>
          int(0)
        }
      }
    }
  }
  ["conflict"]=>
  array(1) {
    [0]=>
    array(3) {
      ["package"]=>
      string(8) "Comments"
      ["conflicts_with"]=>
      string(6) "System"
      ["of_version"]=>
      array(2) {
        [0]=>
        string(1) "<"
        [1]=>
        float(10)
      }
    }
  }
  ["db_support"]=>
  array(1) {
    [0]=>
    string(6) "MySQLi"
  }
  ["storage_support"]=>
  array(1) {
    [0]=>
    string(2) "S3"
  }
}
string(57) "Alternative blogs dependencies (Blogs and Tags installed)"
array(1) {
  ["provide"]=>
  array(1) {
    [0]=>
    array(2) {
      ["package"]=>
      string(5) "Blogs"
      ["features"]=>
      array(1) {
        [0]=>
        string(5) "blogs"
      }
    }
  }
}
string(36) "Try to update Blogs to older version"
array(1) {
  ["update_older"]=>
  array(2) {
    ["from"]=>
    string(14) "2.0.0+build-10"
    ["to"]=>
    string(3) "0.1"
  }
}
string(39) "Try to update Blogs to the same version"
array(1) {
  ["update_same"]=>
  string(14) "2.0.0+build-10"
}
string(36) "Try to update Blogs to newer version"
array(0) {
}
string(75) "Try to update Blogs to newer version (but newer existing version is needed)"
array(1) {
  ["update_from"]=>
  array(3) {
    ["from"]=>
    string(14) "2.0.0+build-10"
    ["to"]=>
    string(4) "99.5"
    ["can_update_from"]=>
    string(4) "98.0"
  }
}
string(76) "Try to install TinyMCE (requires newer version of the system than available)"
array(1) {
  ["require"]=>
  array(1) {
    [0]=>
    array(3) {
      ["package"]=>
      string(6) "System"
      ["existing_version"]=>
      string(13) "1.0.0+build-1"
      ["required_version"]=>
      array(2) {
        [0]=>
        string(2) ">="
        [1]=>
        float(2)
      }
    }
  }
}
string(35) "Get dependent packages (empty meta)"
array(0) {
}
string(32) "Get packages dependent on System"
array(2) {
  [0]=>
  string(5) "Blogs"
  [1]=>
  string(4) "Tags"
}
string(30) "Get packages dependent on Tags"
array(1) {
  [0]=>
  string(5) "Blogs"
}
string(50) "Get packages dependent on Comments (not installed)"
array(0) {
}
