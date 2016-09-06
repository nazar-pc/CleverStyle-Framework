--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
$Config = Config::instance();
var_dump('Default module initially');
var_dump($Config->core['default_module']);

/** @noinspection MkdirRaceConditionInspection */
mkdir(MODULES.'/Existing_module');
file_put_contents(MODULES.'/Existing_module/index.php', '');
$Config->components['modules']['Existing_module'] = [
	'active'  => Config\Module_Properties::ENABLED,
	'db'      => [],
	'storage' => []
];

var_dump('Changed default module (existing)');
$Config->core['default_module'] = 'Existing_module';
var_dump($Config->save());
var_dump($Config->core['default_module']);
rmdir_recursive(MODULES.'/Existing_module');

Config::instance_reset();
var_dump('Default module changed from non-existent module automatically');
$Config = Config::instance();
var_dump($Config->core['default_module']);
unset($Config->components['modules']['Existing_module']);
$Config->save();

var_dump('Changed default module (non-existing)');
$Config->core['default_module'] = 'Non_existent';
var_dump($Config->save());
var_dump($Config->core['default_module']);

var_dump('Apply config');
$Config->core['site_mode'] = 0;
var_dump($Config->apply());
var_dump($Config->core['site_mode']);

var_dump('Cancel applied changes');
$Config->cancel();
var_dump($Config->core['site_mode']);

var_dump('Apply and save config');
$Config->core['site_mode'] = 0;
var_dump($Config->apply());
var_dump($Config->save());
var_dump($Config->core['site_mode']);

var_dump('Cancel saved changes');
$Config->cancel();
var_dump($Config->core['site_mode']);
$Config->core['site_mode'] = 1;
$Config->save();

var_dump('Apply with disabled cache');
Cache::instance_stub(
	[],
	[
		'set' => false
	]
);
var_dump($Config->apply());

var_dump('Failed to load configuration');
Cache::instance_reset();
Cache::instance()->del('/');
DB::instance_stub(
	[],
	[
		'db' => function () {
			$db = new Mock_object(
				[],
				[
					'q'           => false,
					'transaction' => function ($callback) {
						$callback($this);
						return true;
					}
				],
				[]
			);
			return $db;
		}
	]
);
Config::instance_reset();
try {
	Config::instance();
} catch (ExitException $e) {
	var_dump($e->getCode(), $e->getMessage());
}

var_dump('Failed saving');
// Fill cache first
Language::instance_reset();
Config::instance_reset();
DB::instance_reset();
Config::instance();
Config::instance_reset();
DB::instance_stub(
	[],
	[
		'db_prime' => function () {
			$db = new Mock_object(
				[],
				[
					'q'           => false,
					'transaction' => function ($callback) {
						$callback($this);
						return false;
					}
				],
				[]
			);
			return $db;
		}
	]
);
Config::instance_reset();
$Config = Config::instance();
var_dump($Config->save());

var_dump('Base url');
DB::instance_reset();
Config::instance_reset();
Request::instance()->init_from_globals();
$Config = Config::instance();
var_dump($Config->base_url());

var_dump('Base url (multilingual)');
$Config->core['multilingual'] = 1;
var_dump($Config->base_url());

var_dump('Base url (Request not initialized)');
Request::instance()->mirror_index = -1;
var_dump($Config->base_url());

var_dump('Get module');
var_dump($Config->module('System') instanceof Config\Module_Properties);

var_dump('Get non-existing module');
var_dump($Config->module('Non_existent') instanceof False_class);
?>
--EXPECT--
string(24) "Default module initially"
string(6) "System"
string(33) "Changed default module (existing)"
bool(true)
string(15) "Existing_module"
string(61) "Default module changed from non-existent module automatically"
string(6) "System"
string(37) "Changed default module (non-existing)"
bool(true)
string(6) "System"
string(12) "Apply config"
bool(true)
int(0)
string(22) "Cancel applied changes"
int(1)
string(21) "Apply and save config"
bool(true)
bool(true)
int(0)
string(20) "Cancel saved changes"
int(0)
string(25) "Apply with disabled cache"
bool(false)
string(28) "Failed to load configuration"
int(500)
string(35) "Failed to load system configuration"
string(13) "Failed saving"
bool(false)
string(8) "Base url"
string(19) "http://cscms.travis"
string(23) "Base url (multilingual)"
string(22) "http://cscms.travis/en"
string(34) "Base url (Request not initialized)"
string(0) ""
string(10) "Get module"
bool(true)
string(23) "Get non-existing module"
bool(true)
