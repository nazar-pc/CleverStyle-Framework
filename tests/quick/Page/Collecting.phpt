--FILE--
<?php
namespace cs;
require_once __DIR__.'/../../functions.php';
define('DIR', __DIR__.'/Collecting');
define('MODULES', __DIR__.'/Collecting/modules');
define('THEMES', __DIR__.'/Collecting/themes');
include __DIR__.'/../../unit.php';
class Page_test extends Page {
	public static function test () {
		$Config = Config::instance_stub(
			[
				'components' => [
					'modules' => [
						'Disabled_no_map_no_meta'    => [
							'active' => Config\Module_Properties::DISABLED
						],
						'Disabled_with_map_and_meta' => [
							'active' => Config\Module_Properties::DISABLED
						],
						'Enabled_provide'            => [
							'active' => Config\Module_Properties::ENABLED
						],
						'Enabled_no_includes'        => [
							'active' => Config\Module_Properties::ENABLED
						],
						'Enabled_has_dependencies'   => [
							'active' => Config\Module_Properties::ENABLED
						],
						'Enabled_provides_feature'   => [
							'active' => Config\Module_Properties::ENABLED
						],
						'System'                     => [
							'active' => Config\Module_Properties::ENABLED
						],
						'Uninstalled'                => [
							'active' => Config\Module_Properties::UNINSTALLED
						]
					]
				],
				'core'       => [
					'disable_webcomponents' => 0
				]
			]
		);
		Event::instance_stub(
			[],
			[
				'fire' => function (...$arguments) {
					var_dump('cs\Event::fire() called with', $arguments);
					return true;
				}
			]
		);

		$Page        = new self;
		$Page->theme = Config::SYSTEM_THEME;

		var_dump('Regular includes for system theme');
		echo json_encode($Page->get_includes_dependencies_and_map($Config), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n";

		Event::instance_stub(
			[],
			[
				'fire' => function () {
					return true;
				}
			]
		);

		var_dump('Regular includes for custom theme');
		$Page->theme = 'Custom';
		echo json_encode($Page->get_includes_dependencies_and_map($Config), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n";

		var_dump('Regular includes for custom theme (disabled WebComponents)');
		$Config->core['disable_webcomponents'] = 1;
		echo json_encode($Page->get_includes_dependencies_and_map($Config), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n";
	}
}
Page_test::test();
?>
--EXPECTF--
string(33) "Regular includes for system theme"
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(41) "System/Page/includes_dependencies_and_map"
  [1]=>
  array(2) {
    ["dependencies"]=>
    &array(2) {
      ["Enabled_has_dependencies"]=>
      array(2) {
        [0]=>
        string(7) "enabled"
        [1]=>
        string(8) "Enabled3"
      }
      ["Enabled3"]=>
      array(1) {
        [0]=>
        string(24) "Enabled_provides_feature"
      }
    }
    ["includes_map"]=>
    &array(6) {
      ["Disabled_with_map_and_meta"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/html/file1.html"
        }
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file2.js"
        }
      }
      ["Enabled_provide"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/html/file1.html"
        }
        ["js"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file1.js"
        }
      }
      ["Enabled_has_dependencies"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/html/file1.html"
        }
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file2.js"
        }
      }
      ["Enabled_provides_feature"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/html/file1.html"
        }
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file2.js"
        }
      }
      ["admin/System"]=>
      array(2) {
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/includes/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/includes/js/file2.js"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/includes/html/file1.html"
        }
      }
      ["System"]=>
      array(3) {
        ["html"]=>
        array(3) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/includes/html/file1.html"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/html/file1.html"
          [2]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/html/file1.html"
        }
        ["js"]=>
        array(9) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/includes/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/includes/js/file2.js"
          [2]=>
          string(%d) "%s/tests/quick/Page/Collecting/includes/js/Polymer/a.Polymer.js"
          [3]=>
          string(%d) "%s/tests/quick/Page/Collecting/includes/js/Polymer/b.Polymer.behavior.js"
          [4]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file1.js"
          [5]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file2.js"
          [6]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file1.js"
          [7]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file2.js"
          [11]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file2.js"
        }
        ["css"]=>
        array(4) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/includes/css/file1.css"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/css/file1.css"
          [2]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/css/file1.css"
          [7]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/includes/css/file1.css"
        }
      }
    }
  }
}
[
    {
        "Enabled_has_dependencies": [
            "Enabled_provide",
            "Enabled_provides_feature",
            "Enabled3"
        ],
        "Enabled3": [
            "Enabled_provides_feature"
        ]
    },
    {
        "Disabled_with_map_and_meta": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file2.js"
            ]
        },
        "Enabled_provide": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file1.js"
            ]
        },
        "Enabled_has_dependencies": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file2.js"
            ]
        },
        "Enabled_provides_feature": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file2.js"
            ]
        },
        "admin/System": {
            "js": [
                "%s/tests/quick/Page/Collecting/modules/System/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/System/includes/js/file2.js"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/System/includes/html/file1.html"
            ]
        },
        "System": {
            "html": [
                "%s/tests/quick/Page/Collecting/includes/html/file1.html",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/html/file1.html",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/includes/js/file2.js",
                "%s/tests/quick/Page/Collecting/includes/js/Polymer/a.Polymer.js",
                "%s/tests/quick/Page/Collecting/includes/js/Polymer/b.Polymer.behavior.js",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file1.js",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file2.js"
            ],
            "css": [
                "%s/tests/quick/Page/Collecting/includes/css/file1.css",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/System/includes/css/file1.css"
            ]
        }
    }
]
string(33) "Regular includes for custom theme"
[
    {
        "Enabled_has_dependencies": [
            "Enabled_provide",
            "Enabled_provides_feature",
            "Enabled3"
        ],
        "Enabled3": [
            "Enabled_provides_feature"
        ]
    },
    {
        "Disabled_with_map_and_meta": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file2.js"
            ]
        },
        "Enabled_provide": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file1.js"
            ]
        },
        "Enabled_has_dependencies": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file2.js"
            ]
        },
        "Enabled_provides_feature": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file2.js"
            ]
        },
        "admin/System": {
            "js": [
                "%s/tests/quick/Page/Collecting/modules/System/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/System/includes/js/file2.js"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/System/includes/html/file1.html"
            ]
        },
        "System": {
            "html": [
                "%s/tests/quick/Page/Collecting/includes/html/file1.html",
                "%s/tests/quick/Page/Collecting/themes/Custom/html/file1.html",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/includes/js/file2.js",
                "%s/tests/quick/Page/Collecting/includes/js/Polymer/a.Polymer.js",
                "%s/tests/quick/Page/Collecting/includes/js/Polymer/b.Polymer.behavior.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file1.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file2.js"
            ],
            "css": [
                "%s/tests/quick/Page/Collecting/includes/css/file1.css",
                "%s/tests/quick/Page/Collecting/themes/Custom/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/System/includes/css/file1.css"
            ]
        }
    }
]
string(58) "Regular includes for custom theme (disabled WebComponents)"
[
    {
        "Enabled_has_dependencies": [
            "Enabled_provide",
            "Enabled_provides_feature",
            "Enabled3"
        ],
        "Enabled3": [
            "Enabled_provides_feature"
        ]
    },
    {
        "Disabled_with_map_and_meta": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/includes/js/file2.js"
            ]
        },
        "Enabled_provide": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file1.js"
            ]
        },
        "Enabled_has_dependencies": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/includes/js/file2.js"
            ]
        },
        "Enabled_provides_feature": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/includes/js/file2.js"
            ]
        },
        "admin/System": {
            "js": [
                "%s/tests/quick/Page/Collecting/modules/System/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/System/includes/js/file2.js"
            ]
        },
        "System": {
            "js": [
                "%s/tests/quick/Page/Collecting/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/includes/js/file2.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file1.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/includes/js/file2.js"
            ],
            "css": [
                "%s/tests/quick/Page/Collecting/includes/css/file1.css",
                "%s/tests/quick/Page/Collecting/themes/Custom/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/includes/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/System/includes/css/file1.css"
            ]
        }
    }
]
