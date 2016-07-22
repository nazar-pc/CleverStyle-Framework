--FILE--
<?php
namespace cs;
include __DIR__.'/../../bootstrap.php';
Config::instance_stub(
	[
		'core' => [
			'multilingual'     => true,
			'language'         => 'English',
			'active_languages' => array_unique(
				array_merge(
					_mb_substr(get_files_list(LANGUAGES, '/^.*?\.php$/i', 'f'), 0, -4) ?: [],
					_mb_substr(get_files_list(LANGUAGES, '/^.*?\.json$/i', 'f'), 0, -5) ?: []
				)
			)
		]
	]
);
Request::instance()->init_server($_SERVER);
$L = Language::instance();
echo $L->system_home."\n";
echo $L->system_admin_users_permissions_for_user('Lil Wayne')."\n";
echo $L->time(20, 's')."\n";
$L->change('Ukrainian');
echo $L->to_locale('1 January, 2000')."\n";

var_dump('Getting untranslated from default language');
$Cache = Cache::instance();
$Cache->set('languages/English', $Cache->get('languages/English') + ['test_english_only' => 'Yes']);
$Cache->del('languages/Ukrainian');
Language::instance_reset();
$L = Language::instance();
var_dump($L->test_english_only);
$L->change('Ukrainian');
var_dump($L->test_english_only);
?>
--EXPECT--
Home
Permissions for the user Lil Wayne
20 seconds
1 Січня, 2000
string(42) "Getting untranslated from default language"
string(3) "Yes"
string(3) "Yes"
