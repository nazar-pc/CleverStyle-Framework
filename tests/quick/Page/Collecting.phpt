--FILE--
<?php
namespace cs;
use
	cs\Page\Assets\Collecting;

require_once __DIR__.'/../../functions.php';
define('DIR', __DIR__.'/Collecting');
define('MODULES', __DIR__.'/Collecting/modules');
define('THEMES', __DIR__.'/Collecting/themes');
include __DIR__.'/../../unit.php';
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
				'Enabled_no_assets'        => [
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

var_dump('Regular assets for system theme');
echo json_encode(Collecting::get_assets_dependencies_and_map($Config, Config::SYSTEM_THEME), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n";

Event::instance_stub(
	[],
	[
		'fire' => function () {
			return true;
		}
	]
);

var_dump('Regular assets for custom theme');
echo json_encode(Collecting::get_assets_dependencies_and_map($Config, 'Custom'), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n";

var_dump('Regular assets for custom theme (disabled WebComponents)');
$Config->core['disable_webcomponents'] = 1;
echo json_encode(Collecting::get_assets_dependencies_and_map($Config, 'Custom'), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)."\n";
?>
--EXPECTF--
string(31) "Regular assets for system theme"
string(28) "cs\Event::fire() called with"
array(2) {
  [0]=>
  string(39) "System/Page/assets_dependencies_and_map"
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
    ["assets_map"]=>
    &array(6) {
      ["Disabled_with_map_and_meta"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/html/file1.html"
        }
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file2.js"
        }
      }
      ["Enabled_provide"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/html/file1.html"
        }
        ["js"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file1.js"
        }
      }
      ["Enabled_has_dependencies"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/html/file1.html"
        }
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file2.js"
        }
      }
      ["Enabled_provides_feature"]=>
      array(3) {
        ["css"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/css/file1.css"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/html/file1.html"
        }
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file2.js"
        }
      }
      ["admin/System"]=>
      array(2) {
        ["js"]=>
        array(2) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/assets/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/assets/js/file2.js"
        }
        ["html"]=>
        array(1) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/assets/html/file1.html"
        }
      }
      ["System"]=>
      array(3) {
        ["html"]=>
        array(3) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/assets/html/file1.html"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/html/file1.html"
          [2]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/html/file1.html"
        }
        ["js"]=>
        array(9) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/assets/js/file1.js"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/assets/js/file2.js"
          [2]=>
          string(%d) "%s/tests/quick/Page/Collecting/assets/js/Polymer/a.Polymer.js"
          [3]=>
          string(%d) "%s/tests/quick/Page/Collecting/assets/js/Polymer/b.Polymer.behavior.js"
          [4]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file1.js"
          [5]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file2.js"
          [6]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file1.js"
          [7]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file2.js"
          [11]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file2.js"
        }
        ["css"]=>
        array(4) {
          [0]=>
          string(%d) "%s/tests/quick/Page/Collecting/assets/css/file1.css"
          [1]=>
          string(%d) "%s/tests/quick/Page/Collecting/themes/CleverStyle/css/file1.css"
          [2]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/css/file1.css"
          [7]=>
          string(%d) "%s/tests/quick/Page/Collecting/modules/System/assets/css/file1.css"
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
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file2.js"
            ]
        },
        "Enabled_provide": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file1.js"
            ]
        },
        "Enabled_has_dependencies": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file2.js"
            ]
        },
        "Enabled_provides_feature": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file2.js"
            ]
        },
        "admin/System": {
            "js": [
                "%s/tests/quick/Page/Collecting/modules/System/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/System/assets/js/file2.js"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/System/assets/html/file1.html"
            ]
        },
        "System": {
            "html": [
                "%s/tests/quick/Page/Collecting/assets/html/file1.html",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/html/file1.html",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/assets/js/file2.js",
                "%s/tests/quick/Page/Collecting/assets/js/Polymer/a.Polymer.js",
                "%s/tests/quick/Page/Collecting/assets/js/Polymer/b.Polymer.behavior.js",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file1.js",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file2.js"
            ],
            "css": [
                "%s/tests/quick/Page/Collecting/assets/css/file1.css",
                "%s/tests/quick/Page/Collecting/themes/CleverStyle/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/System/assets/css/file1.css"
            ]
        }
    }
]
string(31) "Regular assets for custom theme"
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
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file2.js"
            ]
        },
        "Enabled_provide": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file1.js"
            ]
        },
        "Enabled_has_dependencies": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file2.js"
            ]
        },
        "Enabled_provides_feature": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/css/file1.css"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file2.js"
            ]
        },
        "admin/System": {
            "js": [
                "%s/tests/quick/Page/Collecting/modules/System/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/System/assets/js/file2.js"
            ],
            "html": [
                "%s/tests/quick/Page/Collecting/modules/System/assets/html/file1.html"
            ]
        },
        "System": {
            "html": [
                "%s/tests/quick/Page/Collecting/assets/html/file1.html",
                "%s/tests/quick/Page/Collecting/themes/Custom/html/file1.html",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/html/file1.html"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/assets/js/file2.js",
                "%s/tests/quick/Page/Collecting/assets/js/Polymer/a.Polymer.js",
                "%s/tests/quick/Page/Collecting/assets/js/Polymer/b.Polymer.behavior.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file1.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file2.js"
            ],
            "css": [
                "%s/tests/quick/Page/Collecting/assets/css/file1.css",
                "%s/tests/quick/Page/Collecting/themes/Custom/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/System/assets/css/file1.css"
            ]
        }
    }
]
string(56) "Regular assets for custom theme (disabled WebComponents)"
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
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_with_map_and_meta/assets/js/file2.js"
            ]
        },
        "Enabled_provide": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file1.js"
            ]
        },
        "Enabled_has_dependencies": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_has_dependencies/assets/js/file2.js"
            ]
        },
        "Enabled_provides_feature": {
            "css": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/css/file1.css"
            ],
            "js": [
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provides_feature/assets/js/file2.js"
            ]
        },
        "admin/System": {
            "js": [
                "%s/tests/quick/Page/Collecting/modules/System/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/System/assets/js/file2.js"
            ]
        },
        "System": {
            "js": [
                "%s/tests/quick/Page/Collecting/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/assets/js/file2.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file1.js",
                "%s/tests/quick/Page/Collecting/themes/Custom/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file1.js",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/js/file2.js",
                "%s/tests/quick/Page/Collecting/modules/Enabled_provide/assets/js/file2.js"
            ],
            "css": [
                "%s/tests/quick/Page/Collecting/assets/css/file1.css",
                "%s/tests/quick/Page/Collecting/themes/Custom/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/Disabled_no_map_no_meta/assets/css/file1.css",
                "%s/tests/quick/Page/Collecting/modules/System/assets/css/file1.css"
            ]
        }
    }
]
