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
?>
--EXPECT--
Home
Permissions for the user Lil Wayne
20 seconds
1 Січня, 2000
